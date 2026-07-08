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
 * Converts str_ends_with() checks to Pest's toEndWith() matcher.
 *
 * Before: expect(str_ends_with($string, 'suffix'))->toBeTrue()
 * After:  expect($string)->toEndWith('suffix')
 */
final class UseToEndWithRector extends AbstractRector
{
    use ExpectChainValidation;

    private const FUNCTION_NAME = 'str_ends_with';

    private const MATCHER_NAME = 'toEndWith';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts str_ends_with() checks to toEndWith() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(str_ends_with($string, 'World'))->toBeTrue();
expect(str_ends_with($text, $suffix))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($string)->toEndWith('World');
expect($text)->toEndWith($suffix);
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

        // str_ends_with requires 2 arguments: haystack, needle
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
