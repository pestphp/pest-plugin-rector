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
 * Converts array_key_exists() checks to Pest's toHaveKey() matcher.
 *
 * Before: expect(array_key_exists('key', $array))->toBeTrue()
 * After:  expect($array)->toHaveKey('key')
 */
final class UseToHaveKeyRector extends AbstractRector
{
    use ExpectChainValidation;

    private const FUNCTION_NAME = 'array_key_exists';

    private const MATCHER_NAME = 'toHaveKey';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts array_key_exists() checks to toHaveKey() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(array_key_exists('id', $array))->toBeTrue();
expect(array_key_exists($key, $data))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($array)->toHaveKey('id');
expect($data)->toHaveKey($key);
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

        // array_key_exists requires 2 arguments: key, array
        if (count($funcCall->args) !== 2) {
            return null;
        }

        $keyArg = $funcCall->args[0];
        $arrayArg = $funcCall->args[1];

        if (! $keyArg instanceof Arg || ! $arrayArg instanceof Arg) {
            return null;
        }

        if ($this->getType($arrayArg->value)->isArray()->no()) {
            return null;
        }

        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $arrayArg->value,
            self::MATCHER_NAME,
            [new Arg($keyArg->value)],
            $needsNot
        );
    }
}
