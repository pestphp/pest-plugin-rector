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
 * Converts type checking functions to dedicated type matchers
 */
final class UseTypeMatchersRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const TYPE_FUNCTION_TO_MATCHER = [
        'is_array' => 'toBeArray',
        'is_string' => 'toBeString',
        'is_int' => 'toBeInt',
        'is_integer' => 'toBeInt',
        'is_float' => 'toBeFloat',
        'is_double' => 'toBeFloat',
        'is_bool' => 'toBeBool',
        'is_numeric' => 'toBeNumeric',
        'is_callable' => 'toBeCallable',
        'is_iterable' => 'toBeIterable',
        'is_object' => 'toBeObject',
        'is_resource' => 'toBeResource',
        'is_scalar' => 'toBeScalar',
        'is_null' => 'toBeNull',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect(is_array($x))->toBeTrue() to expect($x)->toBeArray()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(is_array($value))->toBeTrue();
expect(is_string($value))->toBeTrue();
expect(is_int($value))->toBeTrue();
expect(is_bool($value))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeArray();
expect($value)->toBeString();
expect($value)->toBeInt();
expect($value)->toBeBool();
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

        if (! $this->isName($node->name, 'toBeTrue')) {
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

        $functionName = $this->getName($expectArg);
        if ($functionName === null || ! isset(self::TYPE_FUNCTION_TO_MATCHER[$functionName])) {
            return null;
        }

        if (count($expectArg->args) !== 1) {
            return null;
        }

        $arg = $expectArg->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        $expectCall->args = [new Arg($arg->value)];

        $node->name = new Identifier(self::TYPE_FUNCTION_TO_MATCHER[$functionName]);

        return $node;
    }
}
