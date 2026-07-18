<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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
     * @param  MethodCall  $node
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

        $newMatcher = $this->getMatcherForLiteral($node, $arg->value);
        if ($newMatcher === null) {
            return null;
        }

        $node->name = new Identifier($newMatcher);
        $node->args = [];

        return $node;
    }

    private function getMatcherForLiteral(MethodCall $node, Node $value): ?string
    {
        $isStrict = $this->isName($node->name, 'toBe');

        if ($value instanceof ConstFetch) {
            $name = mb_strtolower($value->name->toString());

            if (($name === 'true' || $name === 'false') && ($isStrict || $this->isSubjectDefinitely($node, 'boolean'))) {
                return $name === 'true' ? 'toBeTrue' : 'toBeFalse';
            }

            if ($name === 'null' && $isStrict) {
                return 'toBeNull';
            }
        }

        if ($value instanceof Array_ && $value->items === [] && $this->isSubjectDefinitely($node, 'array')) {
            return 'toBeEmpty';
        }

        if ($value instanceof String_ && $value->value === '' && $this->isSubjectDefinitely($node, 'string')) {
            return 'toBeEmpty';
        }

        return null;
    }

    private function isSubjectDefinitely(MethodCall $node, string $type): bool
    {
        $subject = $this->getMatcherSubject($node);
        if (! $subject instanceof Node) {
            return false;
        }

        $subjectType = $this->getType($subject);

        return match ($type) {
            'boolean' => $subjectType->isBoolean()->yes(),
            'array' => $subjectType->isArray()->yes(),
            'string' => $subjectType->isString()->yes(),
            default => false,
        };
    }
}
