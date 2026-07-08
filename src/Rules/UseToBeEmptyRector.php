<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use RectorPest\AbstractRector;
use RectorPest\Concerns\ExpectChainValidation;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts empty() checks and count-zero comparisons to Pest's toBeEmpty() matcher.
 *
 * Before: expect(empty($value))->toBeTrue()
 * Before: expect(count($array))->toBe(0)
 * Before: expect($array)->toHaveCount(0)
 * After:  expect($value)->toBeEmpty()
 */
final class UseToBeEmptyRector extends AbstractRector
{
    use ExpectChainValidation;

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts empty checks and count-zero comparisons to toBeEmpty() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(empty($value))->toBeTrue();
expect(count($array))->toBe(0);
expect($array)->toHaveCount(0);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeEmpty();
expect($array)->toBeEmpty();
expect($array)->toBeEmpty();
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

        // Pattern 1: expect($array)->toHaveCount(0)
        if ($this->isName($node->name, 'toHaveCount') && $this->hasZeroArg($node)) {
            $node->name = new Identifier('toBeEmpty');
            $node->args = [];

            return $node;
        }

        // Pattern 2: expect(count($array))->toBe(0) or ->toEqual(0)
        if ($this->isNames($node->name, ['toBe', 'toEqual']) && $this->hasZeroArg($node)) {
            $expectCall = $this->getExpectFuncCall($node);
            if (! $expectCall instanceof FuncCall) {
                return null;
            }

            $expectArg = $this->getExpectArgument($node);
            if (! $expectArg instanceof FuncCall) {
                return null;
            }

            if (! $this->isNames($expectArg, ['count', 'sizeof'])) {
                return null;
            }

            if (count($expectArg->args) !== 1) {
                return null;
            }

            $countArg = $expectArg->args[0];
            if (! $countArg instanceof Arg) {
                return null;
            }

            $expectCall->args = [new Arg($countArg->value)];
            $node->name = new Identifier('toBeEmpty');
            $node->args = [];

            return $node;
        }

        // Pattern 3: expect(empty($value))->toBeTrue() / toBeFalse()
        if ($this->isNames($node->name, ['toBeTrue', 'toBeFalse'])) {
            $expectCall = $this->getExpectFuncCall($node);
            if (! $expectCall instanceof FuncCall) {
                return null;
            }

            $expectArg = $this->getExpectArgument($node);
            if ($expectArg instanceof Empty_) {
                $methodName = $node->name instanceof Identifier ? $node->name->name : null;
                if ($methodName === null) {
                    return null;
                }

                $needsNot = $methodName === 'toBeFalse';
                if ($this->hasNotModifier($node)) {
                    $needsNot = ! $needsNot;
                }

                $expectCall->args = [new Arg($expectArg->expr)];

                if ($needsNot) {
                    $notProperty = new PropertyFetch($expectCall, 'not');

                    return new MethodCall($notProperty, 'toBeEmpty');
                }

                return new MethodCall($expectCall, 'toBeEmpty');
            }
        }

        return null;
    }

    private function hasZeroArg(MethodCall $node): bool
    {
        if (count($node->args) !== 1) {
            return false;
        }

        $arg = $node->args[0];
        if (! $arg instanceof Arg) {
            return false;
        }

        return $arg->value instanceof Int_ && $arg->value->value === 0;
    }
}
