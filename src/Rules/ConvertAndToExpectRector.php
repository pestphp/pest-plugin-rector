<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConvertAndToExpectRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Splits `->and()` calls in expect() chains into separate expect() statements',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)->and($b)->toBe(20);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($b)->toBe(20);
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
        return [Expression::class];
    }

    /**
     * @param  Expression  $node
     * @return array<Expression>|null
     */
    public function refactor(Node $node): ?array
    {
        if (! $node->expr instanceof MethodCall) {
            return null;
        }

        if (! $this->isExpectChain($node->expr)) {
            return null;
        }

        $segments = [];
        $segmentOutermost = $node->expr;
        $current = $node->expr;
        $child = null;

        while ($current instanceof MethodCall || $current instanceof PropertyFetch) {
            if (! $current instanceof MethodCall || ! $this->isName($current->name, 'and')) {
                $child = $current;
                $current = $current->var;

                continue;
            }

            if (! isset($current->args[0]) || ! $current->args[0] instanceof Arg) {
                return null;
            }

            $andValue = $current->args[0]->value;
            $andValue->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $expectCall = new FuncCall(new Name('expect'), [new Arg($andValue)]);

            if ($child === null) {
                $segments[] = $expectCall;
            } else {
                $child->var = $expectCall;
                $segments[] = $segmentOutermost;
            }

            $segmentOutermost = $current->var;
            $current = $current->var;
            $child = null;
        }

        if ($segments === []) {
            return null;
        }

        $segments[] = $segmentOutermost;
        $segments = array_reverse($segments);

        $newStmts = [];
        foreach ($segments as $index => $segmentExpr) {
            if ($index === 0) {
                $node->expr = $segmentExpr;
                $newStmts[] = $node;

                continue;
            }

            $newStmts[] = new Expression($segmentExpr);
        }

        return $newStmts;
    }
}
