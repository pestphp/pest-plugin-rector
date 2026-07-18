<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class SimplifyExpectNotRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const FLIPPABLE_MATCHERS = [
        'toBeTrue' => 'toBeFalse',
        'toBeFalse' => 'toBeTrue',
    ];

    /**
     * @var array<int, string>
     */
    private const NEGATABLE_MATCHERS = [
        'toBeEmpty',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplifies negated expectations by flipping the matcher (e.g., expect(!$x)->toBeTrue() becomes expect($x)->toBeFalse())',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(!$condition)->toBeTrue();
expect(!$value)->toBeFalse();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($condition)->toBeFalse();
expect($value)->toBeTrue();
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

        if (! $arg->value instanceof BooleanNot) {
            return null;
        }

        if (! $this->isCalledDirectlyOnExpect($node, $expectCall)) {
            return null;
        }

        $finalMethodName = $this->getFinalMethodName($node);
        if ($finalMethodName === null) {
            return null;
        }

        if (isset(self::FLIPPABLE_MATCHERS[$finalMethodName])) {
            $expectCall->args[0] = $this->nodeFactory->createArg($arg->value->expr);

            $this->flipFinalMatcher($node, self::FLIPPABLE_MATCHERS[$finalMethodName]);

            return $node;
        }

        if (in_array($finalMethodName, self::NEGATABLE_MATCHERS, true)) {
            $expectCall->args[0] = $this->nodeFactory->createArg($arg->value->expr);

            $this->toggleNotModifier($node);

            return $node;
        }

        return null;
    }

    private function isCalledDirectlyOnExpect(MethodCall $methodCall, FuncCall $expectCall): bool
    {
        $var = $methodCall->var;

        if ($var instanceof PropertyFetch && $this->isName($var, 'not')) {
            $var = $var->var;
        }

        return $var === $expectCall;
    }

    private function toggleNotModifier(MethodCall $methodCall): void
    {
        if ($methodCall->var instanceof PropertyFetch && $this->isName($methodCall->var, 'not')) {
            $methodCall->var = $methodCall->var->var;

            return;
        }

        $methodCall->var = new PropertyFetch($methodCall->var, 'not');
    }

    private function getFinalMethodName(MethodCall $methodCall): ?string
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        return $methodCall->name->name;
    }

    private function flipFinalMatcher(MethodCall $methodCall, string $newMethodName): void
    {
        $methodCall->name = new Identifier($newMethodName);
    }
}
