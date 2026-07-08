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
 * Converts expect($page->script($expression)) assertions to dedicated browser script assertion
 */
final class UseBrowserScriptAssertionsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($page->script($expression))->toBe($value) to $page->assertScript($expression, $value)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($page->script('document.title'))->toBe('Home Page');
expect($page->script('document.querySelector(".btn").disabled'))->toBe(true);
expect($page->script('1 + 1'))->toEqual(2);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$page->assertScript('document.title', 'Home Page');
$page->assertScript('document.querySelector(".btn").disabled', true);
$page->assertScript('1 + 1', 2);
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

        if (! $this->isNames($node->name, ['toBe', 'toEqual'])) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (! $expectArg instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($expectArg->name, 'script')) {
            return null;
        }

        if (count($expectArg->args) !== 1) {
            return null;
        }

        $args = array_merge($expectArg->args, $node->args);

        return new MethodCall($expectArg->var, new Identifier('assertScript'), $args);
    }
}
