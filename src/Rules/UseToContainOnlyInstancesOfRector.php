<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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
     * @param  MethodCall  $node
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

        if (! $node->var instanceof PropertyFetch || ! $this->isName($node->var->name, 'each')) {
            return null;
        }

        if ($node->var->var instanceof PropertyFetch && $this->isName($node->var->var->name, 'not')) {
            return null;
        }

        $classArg = $node->args[0];
        if (! $classArg instanceof Arg) {
            return null;
        }

        $node->var = $node->var->var;

        $node->name = new Identifier('toContainOnlyInstancesOf');

        return $node;
    }
}
