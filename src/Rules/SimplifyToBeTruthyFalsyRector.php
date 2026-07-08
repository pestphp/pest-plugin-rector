<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts bool cast assertions to toBeTruthy()/toBeFalsy() matchers.
 *
 * Before: expect((bool) $value)->toBeTrue()
 * After:  expect($value)->toBeTruthy()
 */
final class SimplifyToBeTruthyFalsyRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts bool cast assertions to toBeTruthy()/toBeFalsy() matchers',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect((bool) $value)->toBeTrue();
expect((bool) $value)->toBeFalse();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeTruthy();
expect($value)->toBeFalsy();
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

        if (! $this->isNames($node->name, ['toBeTrue', 'toBeFalse'])) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (!$expectArg instanceof Expr) {
            return null;
        }

        // Pattern: expect((bool) $value)->toBeTrue/toBeFalse()
        if ($expectArg instanceof Bool_) {
            $isTrue = $this->isName($node->name, 'toBeTrue');
            $matcher = $isTrue ? 'toBeTruthy' : 'toBeFalsy';

            $expectCall->args = [new Arg($expectArg->expr)];
            $node->name = new Identifier($matcher);

            return $node;
        }

        // Pattern: expect(boolval($value))->toBeTrue/toBeFalse()
        if ($expectArg instanceof FuncCall && $this->isName($expectArg, 'boolval')) {
            if (count($expectArg->args) !== 1) {
                return null;
            }

            $arg = $expectArg->args[0];
            if (! $arg instanceof Arg) {
                return null;
            }

            $isTrue = $this->isName($node->name, 'toBeTrue');
            $matcher = $isTrue ? 'toBeTruthy' : 'toBeFalsy';

            $expectCall->args = [new Arg($arg->value)];
            $node->name = new Identifier($matcher);

            return $node;
        }

        return null;
    }
}
