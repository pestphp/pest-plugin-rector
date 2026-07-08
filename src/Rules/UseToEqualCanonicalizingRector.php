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
 * Converts sort-then-compare patterns to Pest's toEqualCanonicalizing() matcher.
 *
 * Before: expect(sort($a))->toEqual(sort($b))
 * After:  expect($a)->toEqualCanonicalizing($b)
 */
final class UseToEqualCanonicalizingRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts sort-then-compare to toEqualCanonicalizing() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(sort($a))->toEqual(sort($b));
expect(sort($a))->toBe(sort($b));
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toEqualCanonicalizing($b);
expect($a)->toEqualCanonicalizing($b);
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

        if (! $this->isName($expectArg, 'sort')) {
            return null;
        }

        if (count($expectArg->args) !== 1) {
            return null;
        }

        $sortArg = $expectArg->args[0];
        if (! $sortArg instanceof Arg) {
            return null;
        }

        // Check if the comparison arg is also a sort() call
        $compareArg = $node->args[0];
        if (! $compareArg instanceof Arg) {
            return null;
        }

        if (! $compareArg->value instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($compareArg->value, 'sort')) {
            return null;
        }

        if (count($compareArg->value->args) !== 1) {
            return null;
        }

        $compareInnerArg = $compareArg->value->args[0];
        if (! $compareInnerArg instanceof Arg) {
            return null;
        }

        // Replace expect(sort($a))->toEqual(sort($b)) with expect($a)->toEqualCanonicalizing($b)
        $expectCall->args = [new Arg($sortArg->value)];
        $node->name = new Identifier('toEqualCanonicalizing');
        $node->args = [new Arg($compareInnerArg->value)];

        return $node;
    }
}
