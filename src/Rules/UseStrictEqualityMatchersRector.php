<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts strict equality expressions to Pest's toBe() matcher.
 *
 * Before: expect($a === $b)->toBeTrue()
 * After:  expect($a)->toBe($b)
 */
final class UseStrictEqualityMatchersRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts strict equality expressions to toBe() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a === $b)->toBeTrue();
expect($value === 'expected')->toBeTrue();
expect($a !== $b)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe($b);
expect($value)->toBe('expected');
expect($a)->not->toBe($b);
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

        if (! $node->name instanceof Identifier) {
            return null;
        }

        $methodName = $node->name->name;

        if ($methodName !== 'toBeTrue' && $methodName !== 'toBeFalse') {
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

        $comparison = $arg->value;
        $isIdentical = $comparison instanceof Identical;
        $isNotIdentical = $comparison instanceof NotIdentical;

        if (! $isIdentical && ! $isNotIdentical) {
            return null;
        }

        /** @var Identical|NotIdentical $comparison */
        $left = $comparison->left;
        $right = $comparison->right;

        // Update expect() to use the left side
        $expectCall->args[0] = new Arg($left);

        // Determine if we need ->not
        // $a === $b + toBeTrue = toBe
        // $a === $b + toBeFalse = not->toBe
        // $a !== $b + toBeTrue = not->toBe
        // $a !== $b + toBeFalse = toBe
        $needsNot = ($isNotIdentical && $methodName === 'toBeTrue')
            || ($isIdentical && $methodName === 'toBeFalse');

        if ($this->hasNotModifier($node)) {
            $needsNot = ! $needsNot;
        }

        if ($needsNot) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, 'toBe', [new Arg($right)]);
        }

        return new MethodCall($expectCall, 'toBe', [new Arg($right)]);
    }
}
