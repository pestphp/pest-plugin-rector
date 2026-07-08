<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts strlen()/mb_strlen() comparisons to Pest's toHaveLength() matcher.
 *
 * Before: expect(strlen($string))->toBe(10)
 * After:  expect($string)->toHaveLength(10)
 */
final class UseToHaveLengthRector extends AbstractRector
{
    /**
     * @var array<string>
     */
    private const LENGTH_FUNCTIONS = ['strlen', 'mb_strlen'];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts strlen()/mb_strlen() comparisons to toHaveLength() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(strlen($string))->toBe(10);
expect(mb_strlen($text))->toBe(5);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($string)->toHaveLength(10);
expect($text)->toHaveLength(5);
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

        if (! $node->name instanceof Identifier) {
            return null;
        }

        $methodName = $node->name->name;

        // Only handle toBe() and toEqual() with a length argument
        if ($methodName !== 'toBe' && $methodName !== 'toEqual') {
            return null;
        }

        // Need exactly one argument (the expected length)
        if (count($node->args) !== 1) {
            return null;
        }

        $lengthArg = $node->args[0];
        if (! $lengthArg instanceof Arg) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        if (! isset($expectCall->args[0])) {
            return null;
        }

        $arg = $expectCall->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        if (! $arg->value instanceof FuncCall) {
            return null;
        }

        $funcCall = $arg->value;
        if (! $funcCall->name instanceof Name) {
            return null;
        }

        $funcName = $funcCall->name->toString();
        if (! in_array($funcName, self::LENGTH_FUNCTIONS, true)) {
            return null;
        }

        // strlen/mb_strlen requires at least 1 argument (the string)
        if (count($funcCall->args) < 1) {
            return null;
        }

        $stringArg = $funcCall->args[0];
        if (! $stringArg instanceof Arg) {
            return null;
        }

        if ($this->getType($stringArg->value)->isString()->no()) {
            return null;
        }

        // Update expect() to use the string directly
        $expectCall->args[0] = new Arg($stringArg->value);

        return new MethodCall($expectCall, 'toHaveLength', [new Arg($lengthArg->value)]);
    }
}
