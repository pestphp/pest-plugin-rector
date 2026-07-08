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
 * Converts str_starts_with() checks to Pest's toStartWith() matcher.
 *
 * Before: expect(str_starts_with($string, 'prefix'))->toBeTrue()
 * After:  expect($string)->toStartWith('prefix')
 */
final class UseToStartWithRector extends AbstractRector
{
    use ExpectChainValidation;

    private const FUNCTION_NAME = 'str_starts_with';

    private const MATCHER_NAME = 'toStartWith';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts str_starts_with() checks to toStartWith() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(str_starts_with($string, 'Hello'))->toBeTrue();
expect(str_starts_with($text, $prefix))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($string)->toStartWith('Hello');
expect($text)->toStartWith($prefix);
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

        // str_starts_with requires 2 arguments: haystack, needle
        if (count($funcCall->args) !== 2) {
            return null;
        }

        $haystackArg = $funcCall->args[0];
        $needleArg = $funcCall->args[1];

        if (! $haystackArg instanceof Arg || ! $needleArg instanceof Arg) {
            return null;
        }

        if ($this->getType($haystackArg->value)->isString()->no()) {
            return null;
        }

        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $haystackArg->value,
            self::MATCHER_NAME,
            [new Arg($needleArg->value)],
            $needsNot
        );
    }
}
