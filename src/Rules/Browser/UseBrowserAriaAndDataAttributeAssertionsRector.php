<?php

declare(strict_types=1);

namespace RectorPest\Rules\Browser;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts expect($page->attribute()) assertions on aria-* and data-* attributes to dedicated browser assertion methods
 */
final class UseBrowserAriaAndDataAttributeAssertionsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($page->attribute($selector, "aria-*"))->toBe($value) to $page->assertAriaAttribute($selector, $attr, $value) and the data-* equivalent',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($page->attribute('button', 'aria-label'))->toBe('Close');
expect($page->attribute('div', 'data-id'))->toBe('123');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$page->assertAriaAttribute('button', 'label', 'Close');
$page->assertDataAttribute('div', 'id', '123');
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

        if ($this->hasNotModifier($node)) {
            return null;
        }

        if (! $this->isName($node->name, 'toBe')) {
            return null;
        }

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

        $attrArg = $expectArg->args[1];
        if (! $attrArg instanceof Arg) {
            return null;
        }

        if (! $attrArg->value instanceof String_) {
            return null;
        }

        $attrValue = $attrArg->value->value;

        if (str_starts_with($attrValue, 'aria-')) {
            $strippedName = substr($attrValue, strlen('aria-'));
            $assertMethod = 'assertAriaAttribute';
        } elseif (str_starts_with($attrValue, 'data-')) {
            $strippedName = substr($attrValue, strlen('data-'));
            $assertMethod = 'assertDataAttribute';
        } else {
            return null;
        }

        $args = array_merge(
            [$expectArg->args[0], new Arg(new String_($strippedName))],
            $node->args
        );

        return new MethodCall($expectArg->var, new Identifier($assertMethod), $args);
    }
}
