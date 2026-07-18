<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToHaveSameSizeRector extends AbstractRector
{
    /**
     * @var array<string>
     */
    private const COUNT_FUNCTIONS = ['count', 'sizeof'];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect(count($a))->toBe(count($b)) to expect($a)->toHaveSameSize($b)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(count($array1))->toBe(count($array2));
expect($items)->toHaveCount(count($other));
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($array1)->toHaveSameSize($array2);
expect($items)->toHaveSameSize($other);
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

        $expectCall = $this->getMatcherSubjectHolder($node);
        if (! $expectCall instanceof FuncCall && ! $expectCall instanceof MethodCall) {
            return null;
        }

        if ($this->isNames($node->name, ['toBe', 'toEqual'])) {
            if (count($node->args) !== 1) {
                return null;
            }

            $expectArg = $this->getMatcherSubject($node);
            if (! $this->isCountFunction($expectArg)) {
                return null;
            }

            $assertionArg = $node->args[0];
            if (! $assertionArg instanceof Arg) {
                return null;
            }

            if (! $this->isCountFunction($assertionArg->value)) {
                return null;
            }

            $firstArray = $this->getCountedValue($expectArg);
            $secondArray = $this->getCountedValue($assertionArg->value);

            if (! $firstArray instanceof Expr || ! $secondArray instanceof Expr) {
                return null;
            }

            $firstArrayType = $this->getType($firstArray);
            if ($firstArrayType->isArray()->no() && $firstArrayType->isIterable()->no()) {
                return null;
            }

            $expectCall->args = [new Arg($firstArray)];

            $node->name = new Identifier('toHaveSameSize');
            $node->args = [new Arg($secondArray)];

            return $node;
        }

        if ($this->isName($node->name, 'toHaveCount')) {
            if (count($node->args) !== 1) {
                return null;
            }

            $countArg = $node->args[0];
            if (! $countArg instanceof Arg) {
                return null;
            }

            if (! $this->isCountFunction($countArg->value)) {
                return null;
            }

            $secondArray = $this->getCountedValue($countArg->value);
            if (! $secondArray instanceof Expr) {
                return null;
            }

            $expectArgument = $this->getMatcherSubject($node);
            if ($expectArgument instanceof Expr) {
                $expectArgType = $this->getType($expectArgument);
                if ($expectArgType->isArray()->no() && $expectArgType->isIterable()->no()) {
                    return null;
                }
            }

            $node->name = new Identifier('toHaveSameSize');
            $node->args = [new Arg($secondArray)];

            return $node;
        }

        return null;
    }

    private function isCountFunction(?Node $node): bool
    {
        return $node instanceof FuncCall && $this->isNames($node, self::COUNT_FUNCTIONS);
    }

    private function getCountedValue(?Node $node): ?Expr
    {
        if (! $node instanceof FuncCall) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $arg = $node->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        return $arg->value;
    }
}
