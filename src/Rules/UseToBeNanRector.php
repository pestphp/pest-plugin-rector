<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use RectorPest\AbstractRector;
use RectorPest\Concerns\ExpectChainValidation;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts is_nan() checks to Pest's toBeNan() matcher.
 *
 * Before: expect(is_nan($value))->toBeTrue()
 * After:  expect($value)->toBeNan()
 */
final class UseToBeNanRector extends AbstractRector
{
    use ExpectChainValidation;

    private const FUNCTION_NAME = 'is_nan';

    private const MATCHER_NAME = 'toBeNan';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts is_nan() checks to toBeNan() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(is_nan($value))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeNan();
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
        $extracted = $this->extractFunctionFromExpect($node, [self::FUNCTION_NAME]);
        if ($extracted === null) {
            return null;
        }

        $funcCall = $extracted['funcCall'];
        if (count($funcCall->args) !== 1) {
            return null;
        }

        $pathArg = $funcCall->args[0];
        if (! $pathArg instanceof Arg) {
            return null;
        }

        if ($this->getType($pathArg->value)->isFloat()->no() && $this->getType($pathArg->value)->isInteger()->no()) {
            return null;
        }

        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $pathArg->value,
            self::MATCHER_NAME,
            [],
            $needsNot
        );
    }
}
