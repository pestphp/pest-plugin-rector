<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts abs() difference comparisons to toEqualWithDelta() matcher
 */
final class UseToEqualWithDeltaRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect(abs($a - $b) < $delta)->toBeTrue() to expect($a)->toEqualWithDelta($b, $delta)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(abs($a - $b) < 0.001)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toEqualWithDelta($b, 0.001);
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

        if ($node->name->name !== 'toBeTrue') {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (!$expectArg instanceof Expr) {
            return null;
        }

        return $this->handleAbsComparison($expectArg, $node);
    }

    /**
     * Handle abs($a - $b) < $delta or abs($a - $b) <= $delta pattern
     */
    private function handleAbsComparison(Expr $expectArg, MethodCall $node): ?MethodCall
    {
        if (! $expectArg instanceof Smaller && ! $expectArg instanceof SmallerOrEqual) {
            return null;
        }

        $left = $expectArg->left;
        $delta = $expectArg->right;

        if (! $left instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($left, 'abs')) {
            return null;
        }

        if (count($left->args) !== 1) {
            return null;
        }

        $arg = $left->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        if (! $arg->value instanceof Minus) {
            return null;
        }

        $minus = $arg->value;
        $actual = $minus->left;
        $expected = $minus->right;

        $expectCall = $this->getExpectFuncCall($node);
        if (!$expectCall instanceof FuncCall) {
            return null;
        }

        $expectCall->args[0] = new Arg($actual);

        return new MethodCall($expectCall, 'toEqualWithDelta', [
            new Arg($expected),
            new Arg($delta),
        ]);
    }
}
