<?php

declare(strict_types=1);

namespace RectorPest\Rules\Pest2ToPest3;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replaces deprecated ->tap() method with ->defer() for Pest v3
 */
final class TapToDeferRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces deprecated ->tap() method with ->defer() for Pest v3 migration',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value)->tap(fn ($value) => dump($value))->toBe(10);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->defer(fn ($value) => dump($value))->toBe(10);
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

        if (! $this->isName($node->name, 'tap')) {
            return null;
        }

        $node->name = new Identifier('defer');

        return $node;
    }
}
