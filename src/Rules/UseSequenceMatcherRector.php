<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Enum\NodeGroup;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseSequenceMatcherRector extends AbstractRector
{
    private const string SEQUENCE_PARAM_NAME = 'e';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts consecutive indexed expect() calls to sequence()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($items[0])->toBe('a');
expect($items[1])->toBe('b');
expect($items[2])->toBe('c');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($items)->sequence(fn ($e) => $e->toBe('a'), fn ($e) => $e->toBe('b'), fn ($e) => $e->toBe('c'));
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
        $count = count($stmts);
        $i = 0;

        while ($i < $count) {
            $group = $this->collectSequenceGroup($stmts, $i);

            if ($group === null) {
                $newStmts[] = $stmts[$i];
                $i++;

                continue;
            }

            $sequenceCall = $this->buildSequenceCall($group['variable'], $group['chains']);
            if (! $sequenceCall instanceof MethodCall) {
                $newStmts[] = $stmts[$i];
                $i++;

                continue;
            }

            $newExpression = new Expression($sequenceCall);
            $this->copyComments(array_slice($stmts, $i, count($group['chains'])), $newExpression);

            $newStmts[] = $newExpression;
            $i += count($group['chains']);
            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        $this->setStatements($node, $newStmts);

        return $node;
    }

    /**
     * @param  Node\Stmt[]  $stmts
     * @return array{variable: Expr, chains: list<MethodCall>}|null
     */
    private function collectSequenceGroup(array $stmts, int $startPos): ?array
    {
        $chains = [];
        $baseVariable = null;
        $expectedIndex = 0;
        $counter = count($stmts);

        for ($i = $startPos; $i < $counter; $i++) {
            $stmt = $stmts[$i];

            if (! $stmt instanceof Expression) {
                break;
            }

            if (! $stmt->expr instanceof MethodCall) {
                break;
            }

            $methodCall = $stmt->expr;
            if (! $this->isExpectChain($methodCall)) {
                break;
            }

            $expectCall = $this->getExpectFuncCall($methodCall);
            if (! $expectCall instanceof FuncCall) {
                break;
            }

            if (! isset($expectCall->args[0]) || ! $expectCall->args[0] instanceof Arg) {
                break;
            }

            $expectArg = $expectCall->args[0]->value;

            if (! $expectArg instanceof ArrayDimFetch) {
                break;
            }

            if (! $expectArg->dim instanceof Int_) {
                break;
            }

            $index = $expectArg->dim->value;
            if ($index !== $expectedIndex) {
                break;
            }

            $variable = $expectArg->var;

            if (! $baseVariable instanceof Expr) {
                $baseVariable = $variable;
            } elseif (! $this->nodeComparator->areNodesEqual($baseVariable, $variable)) {
                break;
            }

            $chains[] = $methodCall;
            $expectedIndex++;
        }

        if (count($chains) < 2 || ! $baseVariable instanceof Expr) {
            return null;
        }

        return [
            'variable' => $baseVariable,
            'chains' => $chains,
        ];
    }

    /**
     * @param  list<MethodCall>  $chains
     */
    private function buildSequenceCall(Expr $variable, array $chains): ?MethodCall
    {
        $sequenceArgs = [];

        foreach ($chains as $chain) {
            $arrowBody = $this->rebuildChainOnParam($chain);
            if (! $arrowBody instanceof Expr) {
                return null;
            }

            $arrowFunction = new ArrowFunction([
                'params' => [new Param(new Variable(self::SEQUENCE_PARAM_NAME))],
                'expr' => $arrowBody,
            ]);

            $sequenceArgs[] = new Arg($arrowFunction);
        }

        $expectCall = new FuncCall(
            new Name('expect'),
            [new Arg($variable)]
        );

        return new MethodCall(
            $expectCall,
            new Identifier('sequence'),
            $sequenceArgs
        );
    }

    private function rebuildChainOnParam(MethodCall $methodCall): ?Expr
    {
        $calls = [];
        $current = $methodCall;

        while ($current instanceof MethodCall) {
            $calls[] = $current;
            $current = $current->var;
        }

        $paramVar = new Variable(self::SEQUENCE_PARAM_NAME);

        if ($current instanceof PropertyFetch) {
            $base = new PropertyFetch($paramVar, new Identifier('not'));
        } elseif ($current instanceof FuncCall) {
            $base = $paramVar;
        } else {
            return null;
        }

        $result = $base;
        for ($i = count($calls) - 1; $i >= 0; $i--) {
            $result = new MethodCall($result, $calls[$i]->name, $calls[$i]->args);
        }

        return $result;
    }
}
