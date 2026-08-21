<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Enum\NodeGroup;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ChainExpectCallsRector extends AbstractRector implements ConfigurableRectorInterface
{
    public const string MERGE_DIFFERENT_VARIABLES = 'merge_different_variables';

    /**
     * @var list<string>
     */
    private const array SUBJECT_TRANSFORMING_METHODS = ['and', 'json', 'each', 'match', 'sequence', 'unless', 'when'];

    private bool $mergeDifferentVariables = true;

    /**
     * @param  array<string, mixed>  $configuration
     */
    public function configure(array $configuration): void
    {
        $this->mergeDifferentVariables = (bool) ($configuration[self::MERGE_DIFFERENT_VARIABLES] ?? true);
    }

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Chains consecutive expect() calls into a single chained expectation, combining calls on the same value and joining different values with ->and() (configurable)',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($a)->toBeInt();
expect($b)->toBe(10);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)
    ->toBeInt()
    ->and($b)->toBe(10);
CODE_SAMPLE
                    ,
                    [self::MERGE_DIFFERENT_VARIABLES => true]
                ),
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
// with merge_different_variables => false
expect($a)->toBe(10);
expect($a)->toBeInt();
expect($b)->toBe(10);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
// with merge_different_variables => false
expect($a)->toBe(10)
    ->toBeInt();
expect($b)->toBe(10);
CODE_SAMPLE
                    ,
                    [self::MERGE_DIFFERENT_VARIABLES => false]
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }

    public function refactor(Node $node): ?Node
    {
        $stmts = $this->getStatements($node);
        if ($stmts === null) {
            return null;
        }

        $hasChanged = false;

        do {
            $changedInPass = false;

            foreach ($stmts as $key => $stmt) {
                if (! is_int($key)) {
                    continue;
                }

                if (! $stmt instanceof Expression) {
                    continue;
                }

                if (! $stmt->expr instanceof MethodCall) {
                    continue;
                }

                $methodCall = $stmt->expr;
                if (! $this->isExpectChain($methodCall)) {
                    continue;
                }

                $firstExpectArg = $this->getExpectArgument($methodCall);
                if (! $firstExpectArg instanceof Expr) {
                    continue;
                }

                if (! isset($stmts[$key + 1])) {
                    continue;
                }

                $nextStmt = $stmts[$key + 1];
                if (! $nextStmt instanceof Expression) {
                    continue;
                }

                if (! $nextStmt->expr instanceof MethodCall) {
                    continue;
                }

                $nextMethodCall = $nextStmt->expr;
                if (! $this->isExpectChain($nextMethodCall)) {
                    continue;
                }

                $nextExpectArg = $this->getExpectArgument($nextMethodCall);
                if (! $nextExpectArg instanceof Expr) {
                    continue;
                }

                $currentComments = (array) $stmt->getAttribute('comments', []);
                $nextComments = (array) $nextStmt->getAttribute('comments', []);
                if ($currentComments !== []) {
                    continue;
                }

                if ($nextComments !== []) {
                    continue;
                }

                if ($this->nodeComparator->areNodesEqual($firstExpectArg, $nextExpectArg)) {
                    $transformsSubject = $this->transformsSubject($methodCall);

                    if ($transformsSubject && ! $this->mergeDifferentVariables) {
                        continue;
                    }

                    if ($transformsSubject && $this->mergeDifferentVariableChains($stmts, $key)) {
                        $hasChanged = true;
                        $changedInPass = true;
                        break;
                    }

                    if (! $this->isSideEffectFree($firstExpectArg)) {
                        if ($this->mergeDifferentVariables && $this->mergeDifferentVariableChains($stmts, $key)) {
                            $hasChanged = true;
                            $changedInPass = true;

                            break;
                        }

                        continue;
                    }

                    $this->mergeSameVariable($stmts, $key);

                    $hasChanged = true;
                    $changedInPass = true;

                    break;
                }

                if ($this->mergeDifferentVariables && $this->mergeDifferentVariableChains($stmts, $key)) {
                    $hasChanged = true;
                    $changedInPass = true;

                    break;
                }
            }
        } while ($changedInPass);

        if (! $hasChanged) {
            return null;
        }

        $this->setStatements($node, $stmts);

        return $node;
    }

    private function transformsSubject(MethodCall $methodCall): bool
    {
        foreach ($this->collectChainMethods($methodCall) as $method) {
            $nameValue = $method['name'];
            $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;

            if (in_array($name, self::SUBJECT_TRANSFORMING_METHODS, true)) {
                return true;
            }
        }

        return false;
    }

    private function isSideEffectFree(Expr $expr): bool
    {
        if ($expr instanceof InterpolatedString) {
            return array_all(
                $expr->parts,
                fn (Expr|InterpolatedStringPart $part): bool => ! $part instanceof Expr || $this->isSideEffectFree($part),
            );
        }

        if ($expr instanceof Variable
            || $expr instanceof Scalar
            || $expr instanceof ConstFetch
            || $expr instanceof ClassConstFetch
            || $expr instanceof StaticPropertyFetch
        ) {
            return true;
        }

        if ($expr instanceof PropertyFetch || $expr instanceof NullsafePropertyFetch) {
            return $this->isSideEffectFree($expr->var);
        }

        if ($expr instanceof ArrayDimFetch) {
            return $this->isSideEffectFree($expr->var)
                && (! $expr->dim instanceof Expr || $this->isSideEffectFree($expr->dim));
        }

        if ($expr instanceof Array_) {
            return array_all($expr->items, fn (ArrayItem $item): bool => $this->isSideEffectFree($item->value));
        }

        return false;
    }

    private function buildChainedCall(MethodCall $first, MethodCall $second): MethodCall
    {
        $secondMethods = $this->collectChainMethods($second);

        $result = $this->rebuildMethodChain($first, $secondMethods);

        /** @var MethodCall $result */
        return $result;
    }

    /**
     * @param  array<Node\Stmt>  $stmts
     */
    private function mergeSameVariable(array &$stmts, int $key): void
    {
        /** @var Expression $exprStmt */
        $exprStmt = $stmts[$key];
        /** @var Expression $nextExprStmt */
        $nextExprStmt = $stmts[$key + 1];

        $first = $exprStmt->expr;
        $second = $nextExprStmt->expr;

        assert($first instanceof MethodCall);
        assert($second instanceof MethodCall);

        $exprStmt->expr = $this->buildChainedCall($first, $second);

        $this->applyNewlineAttributes($exprStmt->expr);

        $collectedComments = (array) $exprStmt->getAttribute('comments', []);
        $collectedComments = array_merge($collectedComments, (array) $nextExprStmt->getAttribute('comments', []));

        unset($stmts[$key + 1]);
        $stmts = array_values($stmts);

        if ($collectedComments !== []) {
            $filtered = array_values(array_filter($collectedComments, function ($c): bool {
                if (! is_object($c)) {
                    return false;
                }

                if (method_exists($c, 'getText')) {
                    $text = $c->getText();

                    return is_string($text) && mb_trim($text) !== '';
                }

                return true;
            }));

            if ($filtered !== []) {
                $exprStmt->setAttribute('comments', $filtered);
            }
        }
    }

    /**
     * @param  array<Node\Stmt>  $stmts
     */
    private function mergeDifferentVariableChains(array &$stmts, int $key): bool
    {
        /** @var Expression $exprStmt */
        $exprStmt = $stmts[$key];
        /** @var Expression $nextExprStmt */
        $nextExprStmt = $stmts[$key + 1];

        $firstMethodCall = $exprStmt->expr;
        $nextMethodCall = $nextExprStmt->expr;

        /** @var MethodCall $firstMethodCall */
        /** @var MethodCall $nextMethodCall */
        $targetExpectArg = $this->getExpectArgument($nextMethodCall);
        if (! $targetExpectArg instanceof Expr) {
            return false;
        }

        $collectIndex = $key + 1;
        $allSecondMethods = [];
        $collectedComments = (array) $exprStmt->getAttribute('comments', []);
        $targetIsSideEffectFree = $this->isSideEffectFree($targetExpectArg);

        while (isset($stmts[$collectIndex])) {
            if ($collectIndex > $key + 1 && ! $targetIsSideEffectFree) {
                break;
            }

            $currStmt = $stmts[$collectIndex];

            if (! $currStmt instanceof Expression) {
                break;
            }

            $currComments = (array) $currStmt->getAttribute('comments', []);
            if ($currComments !== []) {
                break;
            }

            if (! $currStmt->expr instanceof MethodCall) {
                break;
            }

            $currMethodCall = $currStmt->expr;
            /** @var MethodCall $currMethodCall */
            if (! $this->isExpectChain($currMethodCall)) {
                break;
            }

            $currExpectArg = $this->getExpectArgument($currMethodCall);
            if (! $currExpectArg instanceof Expr) {
                break;
            }

            if (! $this->nodeComparator->areNodesEqual($targetExpectArg, $currExpectArg)) {
                break;
            }

            $methods = $this->collectChainMethods($currMethodCall);
            $allSecondMethods = array_merge($allSecondMethods, $methods);

            $collectedComments = array_merge($collectedComments, (array) $currStmt->getAttribute('comments', []));

            unset($stmts[$collectIndex]);
            $collectIndex++;
        }

        if ($allSecondMethods === []) {
            return false;
        }

        $targetExpectArg->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $andArg = new Arg($targetExpectArg);
        $andCall = new MethodCall($firstMethodCall, 'and', [$andArg]);

        $result = $this->rebuildMethodChain($andCall, $allSecondMethods);

        $exprStmt->expr = $result;

        $this->applyNewlineAttributes($exprStmt->expr);

        if ($collectedComments !== []) {
            $filtered = array_values(array_filter($collectedComments, function ($c): bool {
                if (! is_object($c)) {
                    return false;
                }

                if (method_exists($c, 'getText')) {
                    $text = $c->getText();

                    return is_string($text) && mb_trim($text) !== '';
                }

                return true;
            }));

            if ($filtered !== []) {
                $exprStmt->setAttribute('comments', $filtered);
            }
        }

        $stmts = array_values($stmts);

        return true;
    }
}
