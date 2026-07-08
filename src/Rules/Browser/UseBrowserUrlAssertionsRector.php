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
 * Converts expect($page->url()) assertions to dedicated browser URL assertion methods
 */
final class UseBrowserUrlAssertionsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($page->url())->toBe($url) to $page->assertUrlIs($url)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($page->url())->toBe('https://example.com/home');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$page->assertUrlIs('https://example.com/home');
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

        if (! $this->isName($expectArg->name, 'url')) {
            return null;
        }

        if ($expectArg->args !== []) {
            return null;
        }

        return new MethodCall($expectArg->var, new Identifier('assertUrlIs'), $node->args);
    }
}
