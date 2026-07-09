<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeBetweenRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($value >= $min && $value <= $max)->toBeTrue() to expect($value)->toBeBetween($min, $max)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value >= 1 && $value <= 10)->toBeTrue();
expect($age >= 18 && $age <= 65)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeBetween(1, 10);
expect($age)->toBeBetween(18, 65);
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

        if (! $this->isName($node->name, 'toBeTrue')) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (! $expectArg instanceof BooleanAnd) {
            return null;
        }

        $left = $expectArg->left;
        $right = $expectArg->right;

        if (! $left instanceof GreaterOrEqual || ! $right instanceof SmallerOrEqual) {
            return null;
        }

        if (! $this->nodeComparator->areNodesEqual($left->left, $right->left)) {
            return null;
        }

        $variable = $left->left;
        $min = $left->right;
        $max = $right->right;

        if ($this->getType($variable)->isInteger()->no() && $this->getType($variable)->isFloat()->no()) {
            return null;
        }

        $expectCall->args = [new Arg($variable)];

        $node->name = new Identifier('toBeBetween');
        $node->args = [new Arg($min), new Arg($max)];

        return $node;
    }
}
