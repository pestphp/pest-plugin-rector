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
 * Converts property_exists() checks to Pest's toHaveProperty() matcher.
 *
 * Before: expect(property_exists($object, 'name'))->toBeTrue()
 * After:  expect($object)->toHaveProperty('name')
 */
final class UseToHavePropertyRector extends AbstractRector
{
    use ExpectChainValidation;

    private const FUNCTION_NAME = 'property_exists';

    private const MATCHER_NAME = 'toHaveProperty';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts property_exists() checks to toHaveProperty() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(property_exists($object, 'name'))->toBeTrue();
expect(property_exists($user, 'email'))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($object)->toHaveProperty('name');
expect($user)->toHaveProperty('email');
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

        // property_exists requires 2 arguments: object|class, property
        if (count($funcCall->args) !== 2) {
            return null;
        }

        $objectArg = $funcCall->args[0];
        $propertyArg = $funcCall->args[1];

        if (! $objectArg instanceof Arg || ! $propertyArg instanceof Arg) {
            return null;
        }

        if ($this->getType($objectArg->value)->isObject()->no()) {
            return null;
        }

        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $objectArg->value,
            self::MATCHER_NAME,
            [new Arg($propertyArg->value)],
            $needsNot
        );
    }
}
