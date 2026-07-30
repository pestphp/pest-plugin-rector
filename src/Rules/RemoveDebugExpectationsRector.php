<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveDebugExpectationsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const array DEBUG_METHODS = ['dump', 'dd', 'ray'];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes debug method calls (dump, dd, ray) from expect chains',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($user)->dump()->toBeInstanceOf(User::class);
expect($value)->ray()->toBe(42);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($user)->toBeInstanceOf(User::class);
expect($value)->toBe(42);
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

        if (! $node->name instanceof Identifier) {
            return null;
        }

        if (in_array($node->name->name, self::DEBUG_METHODS, true)) {
            return $node->var;
        }

        if (! $node->var instanceof MethodCall) {
            return null;
        }

        return $this->removeDebugFromChain($node);
    }

    private function removeDebugFromChain(MethodCall $node): ?MethodCall
    {
        $changed = false;
        $current = $node;

        while ($current->var instanceof MethodCall) {
            $inner = $current->var;

            if (! $inner->name instanceof Identifier) {
                $current = $inner;

                continue;
            }

            if (in_array($inner->name->name, self::DEBUG_METHODS, true)) {
                $current->var = $inner->var;
                $changed = true;

                continue;
            }

            $current = $inner;
        }

        return $changed ? $node : null;
    }
}
