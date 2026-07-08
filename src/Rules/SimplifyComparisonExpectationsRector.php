<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts comparison expressions to dedicated comparison matchers
 */
final class SimplifyComparisonExpectationsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($x > 10)->toBeTrue() to expect($x)->toBeGreaterThan(10)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value > 10)->toBeTrue();
expect($value >= 10)->toBeTrue();
expect($value < 5)->toBeTrue();
expect($value <= 5)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeGreaterThan(10);
expect($value)->toBeGreaterThanOrEqual(10);
expect($value)->toBeLessThan(5);
expect($value)->toBeLessThanOrEqual(5);
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

        if (! $this->isName($node->name, 'toBeTrue')) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        return $this->handleComparison($expectArg, $expectCall, $node);
    }

    private function handleComparison(mixed $expectArg, FuncCall $expectCall, MethodCall $node): ?MethodCall
    {
        if ($expectArg instanceof Greater) {
            return $this->transformComparison($expectArg->left, $expectArg->right, 'toBeGreaterThan', $expectCall, $node);
        }

        if ($expectArg instanceof GreaterOrEqual) {
            return $this->transformComparison($expectArg->left, $expectArg->right, 'toBeGreaterThanOrEqual', $expectCall, $node);
        }

        if ($expectArg instanceof Smaller) {
            return $this->transformComparison($expectArg->left, $expectArg->right, 'toBeLessThan', $expectCall, $node);
        }

        if ($expectArg instanceof SmallerOrEqual) {
            return $this->transformComparison($expectArg->left, $expectArg->right, 'toBeLessThanOrEqual', $expectCall, $node);
        }

        return null;
    }

    private function transformComparison(
        Expr $left,
        Expr $right,
        string $matcher,
        FuncCall $expectCall,
        MethodCall $node
    ): MethodCall {
        $expectCall->args = [new Arg($left)];
        $node->name = new Identifier($matcher);
        $node->args = [new Arg($right)];

        return $node;
    }
}
