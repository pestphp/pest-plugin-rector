<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\NodeFinder;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts foreach loops with expect() to ->each modifier
 */
final class UseEachModifierRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts foreach loops with expect() calls to use the ->each modifier',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
foreach ($items as $item) {
    expect($item)->toBeString();
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($items)->each->toBeString();
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
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->stmts) !== 1) {
            return null;
        }

        $stmt = $node->stmts[0];
        if (! $stmt instanceof Expression) {
            return null;
        }

        if (! $stmt->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $stmt->expr;
        if (! $this->isExpectChain($methodCall)) {
            return null;
        }

        $expectArg = $this->getExpectArgument($methodCall);
        if (! $expectArg instanceof Variable) {
            return null;
        }

        if (! $node->valueVar instanceof Variable) {
            return null;
        }

        if (! $this->nodeComparator->areNodesEqual($expectArg, $node->valueVar)) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($methodCall);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        if ($this->loopVariableUsedInChain($methodCall, $node->valueVar)) {
            return null;
        }

        $expectCall->args[0] = $this->nodeFactory->createArg($node->expr);

        $methods = $this->collectChainMethods($methodCall);
        $eachProperty = new PropertyFetch($expectCall, 'each');

        $result = $this->rebuildMethodChain($eachProperty, $methods);

        return new Expression($result);
    }

    /**
     * Check if the loop variable is referenced anywhere in the method chain
     * beyond the initial expect() argument (e.g. in ->and() arguments)
     */
    private function loopVariableUsedInChain(MethodCall $methodCall, Variable $loopVar): bool
    {
        $nodeFinder = new NodeFinder();
        $methods = $this->collectChainMethods($methodCall);

        foreach ($methods as $method) {
            foreach ($method['args'] as $arg) {
                if (! $arg instanceof Arg) {
                    continue;
                }

                $variables = $nodeFinder->findInstanceOf($arg->value, Variable::class);

                foreach ($variables as $variable) {
                    if ($this->nodeComparator->areNodesEqual($variable, $loopVar)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
