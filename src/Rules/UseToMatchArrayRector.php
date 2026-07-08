<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts multiple array element assertions to toMatchArray()
 */
final class UseToMatchArrayRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts multiple array element assertions to toMatchArray() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($array['name'])->toBe('Nuno');
expect($array['email'])->toBe('nuno@example.com');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($array)->toMatchArray(['name' => 'Nuno', 'email' => 'nuno@example.com']);
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

        // Find consecutive expect() calls on the same array with different keys
        $newStmts = [];
        $i = 0;

        while ($i < count($stmts)) {
            $stmt = $stmts[$i];

            if (! $stmt instanceof Expression) {
                $newStmts[] = $stmt;
                $i++;
                continue;
            }

            $firstExpect = $stmt->expr;

            if (! $firstExpect instanceof MethodCall || ! $this->isExpectChain($firstExpect)) {
                $newStmts[] = $stmt;
                $i++;
                continue;
            }

            // Check if this is an expect on an array element
            $firstArray = $this->getArrayFromExpect($firstExpect);
            if (!$firstArray instanceof Node) {
                $newStmts[] = $stmt;
                $i++;
                continue;
            }

            $firstArrayType = $this->getType($firstArray);
            if ($firstArrayType->isArray()->no()) {
                $newStmts[] = $stmt;
                $i++;
                continue;
            }

            // Collect all consecutive expectations on the same array
            $expectations = [
                [
                    'key' => $this->getArrayKey($this->getExpectArgument($firstExpect)),
                    'value' => $this->getExpectedValue($firstExpect),
                    'method' => $this->getAssertionMethod($firstExpect),
                ]
            ];

            $j = $i + 1;

            while ($j < count($stmts)) {
                $nextStmt = $stmts[$j];

                if (! $nextStmt instanceof Expression) {
                    break;
                }

                $nextExpect = $nextStmt->expr;

                if (! $nextExpect instanceof MethodCall || ! $this->isExpectChain($nextExpect)) {
                    break;
                }

                $nextArray = $this->getArrayFromExpect($nextExpect);

                // Must be the same array variable
                if (!$nextArray instanceof Expr || ! $this->nodeComparator->areNodesEqual($firstArray, $nextArray)) {
                    break;
                }

                // Must use toBe() or toEqual()
                $method = $this->getAssertionMethod($nextExpect);
                if ($method === null || ! in_array($method, ['toBe', 'toEqual'], true)) {
                    break;
                }

                $expectations[] = [
                    'key' => $this->getArrayKey($this->getExpectArgument($nextExpect)),
                    'value' => $this->getExpectedValue($nextExpect),
                    'method' => $method,
                ];

                $j++;
            }

            // Need at least 2 expectations to make this worthwhile
            if (count($expectations) >= 2 && $this->allValidExpectations($expectations)) {
                // Create toMatchArray call
                $arrayItems = [];
                foreach ($expectations as $expectation) {
                    if (!$expectation['value'] instanceof Expr) {
                        continue;
                    }

                    $arrayItems[] = new ArrayItem(
                        $expectation['value'],
                        $expectation['key']
                    );
                }

                $matchArray = new Array_($arrayItems);

                // Create new expect($array)->toMatchArray([...])
                $expectCall = $this->getExpectFuncCall($firstExpect);
                if ($expectCall instanceof FuncCall) {
                    $expectCall->args = [new Arg($firstArray)];

                    $newMethodCall = new MethodCall(
                        $expectCall,
                        new Identifier('toMatchArray'),
                        [new Arg($matchArray)]
                    );

                    $newStmts[] = new Expression($newMethodCall);
                    $hasChanged = true;
                }

                $i = $j;
            } else {
                $newStmts[] = $stmt;
                $i++;
            }
        }

        if (! $hasChanged) {
            return null;
        }

        $this->setStatements($node, $newStmts);

        return $node;
    }

    private function getArrayFromExpect(MethodCall $methodCall): ?Expr
    {
        $expectArg = $this->getExpectArgument($methodCall);

        if ($expectArg instanceof ArrayDimFetch) {
            return $expectArg->var;
        }

        return null;
    }

    private function getArrayKey(?Node $node): ?Expr
    {
        if ($node instanceof ArrayDimFetch) {
            return $node->dim;
        }

        return null;
    }

    private function getExpectedValue(MethodCall $methodCall): ?Expr
    {
        if (count($methodCall->args) !== 1) {
            return null;
        }

        $arg = $methodCall->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        return $arg->value;
    }

    private function getAssertionMethod(MethodCall $methodCall): ?string
    {
        if ($this->isName($methodCall->name, 'toBe')) {
            return 'toBe';
        }

        if ($this->isName($methodCall->name, 'toEqual')) {
            return 'toEqual';
        }

        return null;
    }

    /**
     * @param array<array{key: Expr|null, value: Expr|null, method: string|null}> $expectations
     */
    private function allValidExpectations(array $expectations): bool
    {
        foreach ($expectations as $expectation) {
            if ($expectation['key'] === null || $expectation['value'] === null) {
                return false;
            }
        }

        return true;
    }
}
