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
 * Converts is_infinite() checks to Pest's toBeInfinite() matcher.
 *
 * Before: expect(is_infinite($value))->toBeTrue()
 * After:  expect($value)->toBeInfinite()
 */
final class UseToBeInfiniteRector extends AbstractRector
{
    use ExpectChainValidation;

    private const FUNCTION_NAME = 'is_infinite';

    private const MATCHER_NAME = 'toBeInfinite';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts is_infinite() checks to toBeInfinite() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(is_infinite($value))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeInfinite();
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
