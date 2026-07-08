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

/**
 * Converts chained toHaveKey() calls to single toHaveKeys() matcher
 */
final class UseToHaveKeysRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts chained toHaveKey() calls to toHaveKeys() with array of keys',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($array)->toHaveKey('id')->toHaveKey('name')->toHaveKey('email');
expect($data)->toHaveKey('foo')->toHaveKey('bar');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($array)->toHaveKeys(['id', 'name', 'email']);
expect($data)->toHaveKeys(['foo', 'bar']);
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

        if (! $this->isName($node->name, 'toHaveKey')) {
            return null;
        }

        if (! $this->isExpectValueOfType($node, 'array')) {
            return null;
        }

        // Collect all toHaveKey calls in the chain
        $keys = [];
        $current = $node;
        $firstCall = null;

        while ($current instanceof MethodCall && $this->isName($current->name, 'toHaveKey')) {
            // Skip if any of the toHaveKey calls has a ->not modifier
            if ($this->hasNotModifier($current)) {
                return null;
            }

            if (count($current->args) !== 1) {
                return null;
            }

            $arg = $current->args[0];
            if (! $arg instanceof Arg) {
                return null;
            }

            // Only support string literal keys for now
            if (! $arg->value instanceof String_) {
                return null;
            }

            // Prepend to maintain order (we're walking backwards)
            array_unshift($keys, $arg->value);
            $firstCall = $current;
            $current = $current->var;
        }

        // We need at least 2 toHaveKey calls to make this transformation worthwhile
        if (count($keys) < 2) {
            return null;
        }

        /** @var MethodCall $firstCall */
        $keysArray = new Array_(array_map(
            fn (String_ $key): ArrayItem => new ArrayItem($key),
            $keys
        ));

        $firstCall->name = new Identifier('toHaveKeys');
        $firstCall->args = [new Arg($keysArray)];
        $firstCall->var = $current;

        return $firstCall;
    }
}
