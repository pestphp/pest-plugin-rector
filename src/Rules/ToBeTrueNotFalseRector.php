<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ToBeTrueNotFalseRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const array OPPOSITE_MATCHERS = [
        'toBeFalse' => 'toBeTrue',
        'toBeTrue' => 'toBeFalse',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplifies double-negative expectations like ->not->toBeFalse() to ->toBeTrue()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value)->not->toBeFalse();
expect($value)->not->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeTrue();
expect($value)->toBeFalse();
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

        if (! $this->hasNotModifier($node)) {
            return null;
        }

        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }

        if (! isset(self::OPPOSITE_MATCHERS[$methodName])) {
            return null;
        }

        $expectArgument = $this->getMatcherSubject($node);
        if (! $expectArgument instanceof Expr) {
            return null;
        }

        if ($this->getType($expectArgument)->isBoolean()->no()) {
            return null;
        }

        $oppositeMatcher = self::OPPOSITE_MATCHERS[$methodName];

        return $this->removeNotAndReplaceMatcher($node, $oppositeMatcher);
    }

    private function removeNotAndReplaceMatcher(MethodCall $methodCall, string $newMatcher): ?MethodCall
    {
        $notProperty = $methodCall->var;

        if (! $notProperty instanceof PropertyFetch) {
            return null;
        }

        $methodCall->var = $notProperty->var;
        $methodCall->name = new Identifier($newMatcher);

        return $methodCall;
    }
}
