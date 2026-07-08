<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts count() comparisons to toHaveCount() matcher
 */
final class UseToHaveCountRector extends AbstractRector
{
    /**
     * @var array<string>
     */
    private const COUNT_FUNCTIONS = ['count', 'sizeof'];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect(count($arr))->toBe(5) to expect($arr)->toHaveCount(5)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(count($array))->toBe(5);
expect(count($items))->toEqual(3);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($array)->toHaveCount(5);
expect($items)->toHaveCount(3);
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

        if (! $this->isNames($node->name, ['toBe', 'toEqual'])) {
            return null;
        }

        if (count($node->args) !== 1) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (! $expectArg instanceof FuncCall) {
            return null;
        }

        if (! $this->isNames($expectArg, self::COUNT_FUNCTIONS)) {
            return null;
        }

        if (count($expectArg->args) !== 1) {
            return null;
        }

        $countArg = $expectArg->args[0];
        if (! $countArg instanceof Arg) {
            return null;
        }

        if ($this->getType($countArg->value)->isArray()->no() && $this->getType($countArg->value)->isIterable()->no()) {
            return null;
        }

        $expectCall->args = [new Arg($countArg->value)];

        $node->name = new Identifier('toHaveCount');

        return $node;
    }
}
