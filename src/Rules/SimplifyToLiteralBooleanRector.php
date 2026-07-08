<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Simplifies toBe/toEqual with literal values to dedicated matchers
 */
final class SimplifyToLiteralBooleanRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplifies expect($x)->toBe(true) to expect($x)->toBeTrue() and similar patterns',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value)->toBe(true);
expect($value)->toBe(false);
expect($value)->toBe(null);
expect($value)->toEqual([]);
expect($value)->toBe('');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeTrue();
expect($value)->toBeFalse();
expect($value)->toBeNull();
expect($value)->toBeEmpty();
expect($value)->toBeEmpty();
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

        if (! $this->isNames($node->name, ['toBe', 'toEqual'])) {
            return null;
        }

        if (count($node->args) !== 1) {
            return null;
        }

        $arg = $node->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        $newMatcher = $this->getMatcherForLiteral($arg->value);
        if ($newMatcher === null) {
            return null;
        }

        $node->name = new Identifier($newMatcher);
        $node->args = [];

        return $node;
    }

    private function getMatcherForLiteral(Node $value): ?string
    {
        if ($value instanceof ConstFetch) {
            $name = strtolower($value->name->toString());

            if ($name === 'true') {
                return 'toBeTrue';
            }

            if ($name === 'false') {
                return 'toBeFalse';
            }

            if ($name === 'null') {
                return 'toBeNull';
            }
        }

        if ($value instanceof Array_ && $value->items === []) {
            return 'toBeEmpty';
        }

        if ($value instanceof String_ && $value->value === '') {
            return 'toBeEmpty';
        }

        return null;
    }
}
