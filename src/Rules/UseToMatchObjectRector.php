<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
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
 * Converts consecutive toHaveProperty() assertions with values on the same object to toMatchObject()
 */
final class UseToMatchObjectRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts consecutive toHaveProperty() with values to toMatchObject() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($user)->toHaveProperty('name', 'Nuno');
expect($user)->toHaveProperty('email', 'nuno@example.com');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($user)->toMatchObject(['name' => 'Nuno', 'email' => 'nuno@example.com']);
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

            $firstProperty = $this->extractPropertyWithValue($firstExpect);
            if ($firstProperty === null) {
                $newStmts[] = $stmt;
                $i++;

                continue;
            }

            $firstSubject = $this->getExpectArgument($firstExpect);
            if (! $firstSubject instanceof Expr) {
                $newStmts[] = $stmt;
                $i++;

                continue;
            }

            $firstSubjectType = $this->getType($firstSubject);
            if ($firstSubjectType->isObject()->no()) {
                $newStmts[] = $stmt;
                $i++;

                continue;
            }

            $properties = [$firstProperty];
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

                $nextSubject = $this->getExpectArgument($nextExpect);
                if (! $nextSubject instanceof Expr || ! $this->nodeComparator->areNodesEqual($firstSubject, $nextSubject)) {
                    break;
                }

                $nextProperty = $this->extractPropertyWithValue($nextExpect);
                if ($nextProperty === null) {
                    break;
                }

                $properties[] = $nextProperty;
                $j++;
            }

            if (count($properties) >= 2) {
                $arrayItems = [];
                foreach ($properties as $property) {
                    $arrayItems[] = new ArrayItem($property['value'], $property['key']);
                }

                $matchArray = new Array_($arrayItems);

                $expectCall = $this->getExpectFuncCall($firstExpect);
                if ($expectCall instanceof FuncCall) {
                    $newMethodCall = new MethodCall(
                        $expectCall,
                        new Identifier('toMatchObject'),
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

    /**
     * Extract property name and value from toHaveProperty('key', 'value') call.
     *
     * @return array{key: Expr, value: Expr}|null
     */
    private function extractPropertyWithValue(MethodCall $methodCall): ?array
    {
        if (! $this->isName($methodCall->name, 'toHaveProperty')) {
            return null;
        }

        if (count($methodCall->args) !== 2) {
            return null;
        }

        $keyArg = $methodCall->args[0];
        $valueArg = $methodCall->args[1];

        if (! $keyArg instanceof Arg || ! $valueArg instanceof Arg) {
            return null;
        }

        return [
            'key' => $keyArg->value,
            'value' => $valueArg->value,
        ];
    }
}
