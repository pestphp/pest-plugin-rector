<?php

declare(strict_types=1);

namespace RectorPest\Rules\Browser;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts expect($page->value($selector)) assertions to dedicated browser value assertion methods
 */
final class UseBrowserValueAssertionsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($page->value($selector))->toBe($value) to $page->assertValue($selector, $value)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($page->value('input[name=email]'))->toBe('test@example.com');
expect($page->value('input[name=email]'))->not->toBe('wrong@example.com');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$page->assertValue('input[name=email]', 'test@example.com');
$page->assertValueIsNot('input[name=email]', 'wrong@example.com');
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

        $hasNot = $this->hasNotModifier($node);

        if (! $this->isName($node->name, 'toBe')) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (! $expectArg instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($expectArg->name, 'value')) {
            return null;
        }

        if (count($expectArg->args) !== 1) {
            return null;
        }

        $assertionMethod = $hasNot ? 'assertValueIsNot' : 'assertValue';

        $args = array_merge($expectArg->args, $node->args);

        return new MethodCall($expectArg->var, new Identifier($assertionMethod), $args);
    }
}
