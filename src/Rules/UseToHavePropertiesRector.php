<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToHavePropertiesRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts chained toHaveProperty() calls to toHaveProperties() with array of properties',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($user)->toHaveProperty('name')->toHaveProperty('email');
expect($object)->toHaveProperty('foo')->toHaveProperty('bar')->toHaveProperty('baz');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($user)->toHaveProperties(['name', 'email']);
expect($object)->toHaveProperties(['foo', 'bar', 'baz']);
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

        if (! $this->isName($node->name, 'toHaveProperty')) {
            return null;
        }

        if (! $this->isExpectValueOfType($node, 'object')) {
            return null;
        }

        $properties = [];
        $current = $node;
        $firstCall = null;

        while ($current instanceof MethodCall && $this->isName($current->name, 'toHaveProperty')) {
            if ($this->hasNotModifier($current)) {
                return null;
            }

            if (count($current->args) < 1) {
                return null;
            }

            if (count($current->args) > 1) {
                return null;
            }

            $arg = $current->args[0];
            if (! $arg instanceof Arg) {
                return null;
            }

            if (! $arg->value instanceof String_) {
                return null;
            }

            array_unshift($properties, $arg->value);
            $firstCall = $current;
            $current = $current->var;
        }

        if (count($properties) < 2) {
            return null;
        }

        /** @var MethodCall $firstCall */
        $propertiesArray = new Array_(array_map(
            fn (String_ $property): ArrayItem => new ArrayItem($property),
            $properties
        ));

        $firstCall->name = new Identifier('toHaveProperties');
        $firstCall->args = [new Arg($propertiesArray)];
        $firstCall->var = $current;

        return $firstCall;
    }
}
