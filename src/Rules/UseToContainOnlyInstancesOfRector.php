<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use RuntimeException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts ->each->toBeInstanceOf() pattern to toContainOnlyInstancesOf()
 */
final class UseToContainOnlyInstancesOfRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts ->each->toBeInstanceOf() pattern to toContainOnlyInstancesOf() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($items)->each->toBeInstanceOf(User::class);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($items)->toContainOnlyInstancesOf(User::class);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isExpectChain($node)) {
            return null;
        }

        if (! $this->isName($node->name, 'toBeInstanceOf')) {
            return null;
        }

        if (! $this->isExpectValueOfType($node, 'iterable')) {
            return null;
        }

        if (count($node->args) !== 1) {
            return null;
        }

        // Check if there's an ->each property fetch in the chain
        if (! $this->hasEachModifier($node)) {
            return null;
        }

        // Get the class argument
        $classArg = $node->args[0];
        if (! $classArg instanceof Arg) {
            return null;
        }

        // Remove the 'each' property fetch from the chain
        $node->var = $this->removeEachFromChain($node->var);

        // Replace toBeInstanceOf() with toContainOnlyInstancesOf()
        $node->name = new Identifier('toContainOnlyInstancesOf');

        return $node;
    }

    private function hasEachModifier(MethodCall $methodCall): bool
    {
        $var = $methodCall->var;

        while ($var instanceof PropertyFetch) {
            if ($this->isName($var->name, 'each')) {
                return true;
            }

            $var = $var->var;
        }

        return false;
    }

    private function removeEachFromChain(Node $node): Expr
    {
        if ($node instanceof PropertyFetch && $this->isName($node->name, 'each')) {
            return $node->var;
        }

        if ($node instanceof Expr) {
            return $node;
        }

        throw new RuntimeException('Node is not an Expr');
    }
}
