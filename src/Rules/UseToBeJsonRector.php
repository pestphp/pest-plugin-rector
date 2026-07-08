<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts json_decode() null checks to Pest's toBeJson() matcher.
 *
 * Before: expect(json_decode($string) !== null)->toBeTrue()
 * After:  expect($string)->toBeJson()
 */
final class UseToBeJsonRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts json_decode() null checks to toBeJson() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(json_decode($string) !== null)->toBeTrue();
expect(json_decode($json) === null)->toBeFalse();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($string)->toBeJson();
expect($json)->toBeJson();
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

        if ($methodName !== 'toBeTrue' && $methodName !== 'toBeFalse') {
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

        // Check for json_decode($x) !== null or json_decode($x) === null
        $comparison = $arg->value;
        $isNotIdentical = $comparison instanceof NotIdentical;
        $isIdentical = $comparison instanceof Identical;

        if (! $isNotIdentical && ! $isIdentical) {
            return null;
        }

        /** @var Identical|NotIdentical $comparison */
        $jsonDecodeCall = $this->extractJsonDecodeCall($comparison);
        if (! $jsonDecodeCall instanceof FuncCall) {
            return null;
        }

        if (count($jsonDecodeCall->args) < 1) {
            return null;
        }

        $stringArg = $jsonDecodeCall->args[0];
        if (! $stringArg instanceof Arg) {
            return null;
        }

        if ($this->getType($stringArg->value)->isString()->no()) {
            return null;
        }

        // Determine if result should be positive (toBeJson) or negative (not->toBeJson)
        // json_decode($x) !== null + toBeTrue = valid JSON = toBeJson
        // json_decode($x) !== null + toBeFalse = invalid JSON = not->toBeJson
        // json_decode($x) === null + toBeTrue = invalid JSON = not->toBeJson
        // json_decode($x) === null + toBeFalse = valid JSON = toBeJson
        $expectsValidJson = ($isNotIdentical && $methodName === 'toBeTrue')
            || ($isIdentical && $methodName === 'toBeFalse');

        // Update expect() to use the string directly
        $expectCall->args[0] = new Arg($stringArg->value);

        if (! $expectsValidJson) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, 'toBeJson');
        }

        return new MethodCall($expectCall, 'toBeJson');
    }

    private function isNull(Node $node): bool
    {
        return $node instanceof ConstFetch
            && strtolower($node->name->toString()) === 'null';
    }

    /**
     * Extract json_decode() call from a comparison with null.
     */
    private function extractJsonDecodeCall(Identical|NotIdentical $comparison): ?FuncCall
    {
        $funcCall = null;

        if ($comparison->left instanceof FuncCall && $this->isNull($comparison->right)) {
            $funcCall = $comparison->left;
        } elseif ($comparison->right instanceof FuncCall && $this->isNull($comparison->left)) {
            $funcCall = $comparison->right;
        }

        if (! $funcCall instanceof FuncCall) {
            return null;
        }

        if (! $funcCall->name instanceof Name) {
            return null;
        }

        if ($funcCall->name->toString() !== 'json_decode') {
            return null;
        }

        return $funcCall;
    }
}
