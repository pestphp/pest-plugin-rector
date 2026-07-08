<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Simplifies negated expectations by flipping the matcher method.
 *
 * Converts expect(!$x)->toBeTrue() to expect($x)->toBeFalse()
 * and expect(!$x)->toBeFalse() to expect($x)->toBeTrue().
 */
final class SimplifyExpectNotRector extends AbstractRector
{
    /**
     * Map of matcher methods that can be flipped when negation is removed.
     *
     * @var array<string, string>
     */
    private const FLIPPABLE_MATCHERS = [
        'toBeTrue' => 'toBeFalse',
        'toBeFalse' => 'toBeTrue',
        'toBeEmpty' => 'toBeNotEmpty',
        'toBeNotEmpty' => 'toBeEmpty',
        'toBeNull' => 'toBeNotNull',
        'toBeNotNull' => 'toBeNull',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplifies negated expectations by flipping the matcher (e.g., expect(!$x)->toBeTrue() becomes expect($x)->toBeFalse())',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(!$condition)->toBeTrue();
expect(!$value)->toBeFalse();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($condition)->toBeFalse();
expect($value)->toBeTrue();
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

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        if (! isset($expectCall->args[0])) {
            return null;
        }

        $arg = $expectCall->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        if (! $arg->value instanceof BooleanNot) {
            return null;
        }

        // Get the final method call in the chain to check if it's flippable
        $finalMethodName = $this->getFinalMethodName($node);
        if ($finalMethodName === null || ! isset(self::FLIPPABLE_MATCHERS[$finalMethodName])) {
            return null;
        }

        // Remove the negation from expect() argument
        $negatedExpression = $arg->value->expr;
        $expectCall->args[0] = $this->nodeFactory->createArg($negatedExpression);

        // Flip the matcher method
        $this->flipFinalMatcher($node, self::FLIPPABLE_MATCHERS[$finalMethodName]);

        return $node;
    }

    /**
     * Get the name of the final method in the expect chain.
     */
    private function getFinalMethodName(MethodCall $methodCall): ?string
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        return $methodCall->name->name;
    }

    /**
     * Flip the final matcher method to its opposite.
     */
    private function flipFinalMatcher(MethodCall $methodCall, string $newMethodName): void
    {
        $methodCall->name = new Identifier($newMethodName);
    }
}
