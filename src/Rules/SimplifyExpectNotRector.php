<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
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
        'toBeEmpty' => 'toBeNotEmpty',
        'toBeNotEmpty' => 'toBeEmpty',
        'toBeNull' => 'toBeNotNull',
        'toBeNotNull' => 'toBeNull',
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

        $finalMethodName = $this->getFinalMethodName($node);
        if ($finalMethodName === null || ! isset(self::FLIPPABLE_MATCHERS[$finalMethodName])) {
            return null;
        }

        $negatedExpression = $arg->value->expr;
        $expectCall->args[0] = $this->nodeFactory->createArg($negatedExpression);

        $this->flipFinalMatcher($node, self::FLIPPABLE_MATCHERS[$finalMethodName]);

        return $node;
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
