<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ChainExpectCallsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Chains multiple expect() calls on the same value into a single chained expectation',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($a)->toBeInt();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)
    ->toBeInt();
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($b)->toBe(10);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)
    ->and($b)->toBe(10);
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($a)->toBeInt();
expect($b)->toBe(10);
expect($b)->toBeInt();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)
    ->toBeInt()
    ->and($b)->toBe(10)
    ->toBeInt();
CODE_SAMPLE
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
                    $currentMethods = $this->collectChainMethods($methodCall);
                    $hasAnd = false;
                    foreach ($currentMethods as $cm) {
                        $nameValue = $cm['name'];
                        $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;
                        if ($name === 'and') {
                            $hasAnd = true;
                            break;
                        }
                    }

                    if ($hasAnd && $this->mergeDifferentVariableChains($stmts, $key)) {
                        $hasChanged = true;
                        $changedInPass = true;
                        break;
                    }

                    $this->mergeSameVariable($stmts, $key);

                    $hasChanged = true;
                    $changedInPass = true;

                    break;
                }

                if ($this->mergeDifferentVariableChains($stmts, $key)) {
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

        while (isset($stmts[$collectIndex])) {
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

    private function applyNewlineAttributes(Expr $chain): void
    {
        if (! defined(AttributeKey::class.'::NEWLINE_ON_FLUENT_CALL')) {
            return;
        }

        $current = $chain;

        while ($current instanceof MethodCall) {
            $var = $current->var;

            if ($var instanceof FuncCall) {
                break;
            }

            if ($var instanceof PropertyFetch) {
                $current = $var->var;

                continue;
            }

            if (! $var instanceof MethodCall) {
                break;
            }

            if ($this->isName($var->name, 'and')) {
                $var->setAttribute(AttributeKey::NEWLINE_ON_FLUENT_CALL, true);
                $current = $var->var;

                continue;
            }

            $current->setAttribute(AttributeKey::NEWLINE_ON_FLUENT_CALL, true);
            $current = $var;
        }
    }
}
