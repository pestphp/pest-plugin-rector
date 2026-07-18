<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use Pest\Rector\Concerns\ExpectChainValidation;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeInRector extends AbstractRector
{
    use ExpectChainValidation;

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts strict in_array() checks to the toBeIn() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(in_array($value, ['pending', 'active'], true))->toBeTrue();
expect(in_array($status, $allowedStatuses, true))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeIn(['pending', 'active']);
expect($status)->toBeIn($allowedStatuses);
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
        $extracted = $this->extractFunctionFromExpect($node, ['in_array']);
        if ($extracted === null) {
            return null;
        }

        $funcCall = $extracted['funcCall'];

        if (count($funcCall->args) < 2 || count($funcCall->args) > 3) {
            return null;
        }

        $needleArg = $funcCall->args[0];
        $haystackArg = $funcCall->args[1];

        if (! $needleArg instanceof Arg || ! $haystackArg instanceof Arg) {
            return null;
        }

        if (count($funcCall->args) !== 3) {
            return null;
        }

        $strictArg = $funcCall->args[2];
        if (! $strictArg instanceof Arg || ! $this->isTrue($strictArg->value)) {
            return null;
        }

        if ($this->getType($haystackArg->value)->isArray()->no() && $this->getType($haystackArg->value)->isIterable()->no()) {
            return null;
        }

        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $needleArg->value,
            'toBeIn',
            [new Arg($haystackArg->value)],
            $needsNot
        );
    }
}
