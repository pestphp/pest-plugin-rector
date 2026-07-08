<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Simplifies double-negative expectations to their positive equivalents
 */
final class ToBeTrueNotFalseRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OPPOSITE_MATCHERS = [
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
     * @param MethodCall $node
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

        $expectArgument = $this->getExpectArgument($node);
        if (!$expectArgument instanceof Expr) {
            return null;
        }

        if (! $this->isExpectValueOfType($node, 'boolean')) {
            return null;
        }

        $oppositeMatcher = self::OPPOSITE_MATCHERS[$methodName];

        return $this->removeNotAndReplaceMatcher($node, $oppositeMatcher);
    }

    private function removeNotAndReplaceMatcher(MethodCall $methodCall, string $newMatcher): ?MethodCall
    {
        $expectCall = $this->getExpectFuncCall($methodCall);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        $methods = $this->collectChainMethods($methodCall);

        // remove only the `not` property fetch entries (keep other property fetches like ->each)
        $methods = array_values(array_filter($methods, function (array $m): bool {
            if (empty($m['is_property'])) {
                return true;
            }

            $nameValue = $m['name'];

            $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;

            return $name !== 'not';
        }));

        if ($methods !== []) {
            $lastIndex = count($methods) - 1;
            $methods[$lastIndex] = [
                'name' => new Identifier($newMatcher),
                'args' => $methods[$lastIndex]['args'],
            ];
        }

        $result = $this->rebuildMethodChain($expectCall, $methods);

        return $result instanceof MethodCall ? $result : null;
    }
}
