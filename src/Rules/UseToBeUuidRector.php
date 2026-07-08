<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts UUID regex validation to toBeUuid() matcher
 */
final class UseToBeUuidRector extends AbstractRector
{
    private const UUID_REGEX_PATTERNS = [
        '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
        '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i',
        '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts UUID regex validation to toBeUuid() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value))->toBe(1);
expect(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid))->toBeGreaterThan(0);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeUuid();
expect($uuid)->toBeUuid();
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

        if (! $this->isNames($node->name, ['toBe', 'toBeGreaterThan', 'toEqual'])) {
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

        if (! $this->isName($expectArg, 'preg_match')) {
            return null;
        }

        if (count($expectArg->args) < 2) {
            return null;
        }

        // Get the pattern argument
        $patternArg = $expectArg->args[0];
        if (! $patternArg instanceof Arg) {
            return null;
        }

        if (! $patternArg->value instanceof String_) {
            return null;
        }

        $pattern = $patternArg->value->value;

        // Check if it matches any UUID pattern
        if (! in_array($pattern, self::UUID_REGEX_PATTERNS, true)) {
            return null;
        }

        // Get the subject argument (the value being tested)
        $subjectArg = $expectArg->args[1];
        if (! $subjectArg instanceof Arg) {
            return null;
        }

        if ($this->getType($subjectArg->value)->isString()->no()) {
            return null;
        }

        // Verify the assertion matches what we expect for a successful regex match
        if ($this->isName($node->name, 'toBe')) {
            if (count($node->args) !== 1) {
                return null;
            }

            $arg = $node->args[0];
            if (! $arg instanceof Arg || ! $arg->value instanceof LNumber || $arg->value->value !== 1) {
                return null;
            }
        } elseif ($this->isName($node->name, 'toBeGreaterThan')) {
            if (count($node->args) !== 1) {
                return null;
            }

            $arg = $node->args[0];
            if (! $arg instanceof Arg || ! $arg->value instanceof LNumber || $arg->value->value !== 0) {
                return null;
            }
        }

        // Replace expect(preg_match(...)) with expect($value)
        $expectCall->args = [new Arg($subjectArg->value)];

        // Replace toBe(1) or toBeGreaterThan(0) with toBeUuid()
        $node->name = new Identifier('toBeUuid');
        $node->args = [];

        return $node;
    }
}
