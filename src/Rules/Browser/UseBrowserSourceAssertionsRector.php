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
 * Converts expect($page->content()) assertions to dedicated browser source assertion methods
 */
final class UseBrowserSourceAssertionsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($page->content())->toContain($html) to $page->assertSourceHas($html)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($page->content())->toContain('<h1>Welcome</h1>');
expect($page->content())->not->toContain('<div class="error">');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$page->assertSourceHas('<h1>Welcome</h1>');
$page->assertSourceMissing('<div class="error">');
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

        if (! $this->isName($node->name, 'toContain')) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (! $expectArg instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($expectArg->name, 'content')) {
            return null;
        }

        if ($expectArg->args !== []) {
            return null;
        }

        $hasNot = $this->hasNotModifier($node);
        $assertionMethod = $hasNot ? 'assertSourceMissing' : 'assertSourceHas';

        return new MethodCall($expectArg->var, new Identifier($assertionMethod), $node->args);
    }
}
