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
 * Converts expect($page->attribute($selector, $attr)) assertions to dedicated browser attribute assertion methods
 */
final class UseBrowserAttributeAssertionsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($page->attribute($selector, $attr))->toBe($value) to $page->assertAttribute($selector, $attr, $value)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($page->attribute('img', 'alt'))->toBe('Profile Picture');
expect($page->attribute('div', 'class'))->toContain('container');
expect($page->attribute('div', 'class'))->not->toContain('hidden');
expect($page->attribute('button', 'disabled'))->toBeNull();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$page->assertAttribute('img', 'alt', 'Profile Picture');
$page->assertAttributeContains('div', 'class', 'container');
$page->assertAttributeDoesntContain('div', 'class', 'hidden');
$page->assertAttributeMissing('button', 'disabled');
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
        $matcherName = $this->getName($node->name);

        $expectArg = $this->getExpectArgument($node);
        if (! $expectArg instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($expectArg->name, 'attribute')) {
            return null;
        }

        if (count($expectArg->args) !== 2) {
            return null;
        }

        $pageVar = $expectArg->var;
        $baseArgs = $expectArg->args;

        if (! $hasNot && $matcherName === 'toBe') {
            $args = array_merge($baseArgs, $node->args);

            return new MethodCall($pageVar, new Identifier('assertAttribute'), $args);
        }

        if (! $hasNot && $matcherName === 'toContain') {
            $args = array_merge($baseArgs, $node->args);

            return new MethodCall($pageVar, new Identifier('assertAttributeContains'), $args);
        }

        if ($hasNot && $matcherName === 'toContain') {
            $args = array_merge($baseArgs, $node->args);

            return new MethodCall($pageVar, new Identifier('assertAttributeDoesntContain'), $args);
        }

        if (! $hasNot && $matcherName === 'toBeNull') {
            return new MethodCall($pageVar, new Identifier('assertAttributeMissing'), $baseArgs);
        }

        // not->toBe is intentionally not transformed: the plugin has no assertAttributeIsNot equivalent.
        return null;
    }
}
