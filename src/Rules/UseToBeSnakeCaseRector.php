<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts Str::snake() equality checks to toBeSnakeCase() matcher.
 * Requires illuminate/support (Laravel).
 */
final class UseToBeSnakeCaseRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts Str::snake() equality checks to toBeSnakeCase() matcher (requires illuminate/support)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(Str::snake($value) === $value)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeSnakeCase();
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
        if (! $expectArg instanceof Identical) {
            return null;
        }

        // Pattern 1: Str::snake($value) === $value
        if ($this->isStrMethod($expectArg->left, 'snake')) {
            $staticCall = $expectArg->left;
            if ($this->nodeComparator->areNodesEqual($this->getFirstStaticArg($staticCall), $expectArg->right)) {
                if ($this->getType($expectArg->right)->isString()->no()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->right)];
                $node->name = new Identifier('toBeSnakeCase');

                return $node;
            }
        }

        // Pattern 2: $value === Str::snake($value)
        if ($this->isStrMethod($expectArg->right, 'snake')) {
            $staticCall = $expectArg->right;
            if ($this->nodeComparator->areNodesEqual($expectArg->left, $this->getFirstStaticArg($staticCall))) {
                if ($this->getType($expectArg->left)->isString()->no()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->left)];
                $node->name = new Identifier('toBeSnakeCase');

                return $node;
            }
        }

        return null;
    }

    private function isStrMethod(?Node $node, string $method): bool
    {
        return $node instanceof StaticCall
            && $this->isName($node->class, 'Illuminate\Support\Str')
            && $this->isName($node->name, $method);
    }

    private function getFirstStaticArg(?Node $node): ?Node
    {
        if (! $node instanceof StaticCall) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $arg = $node->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        return $arg->value;
    }
}
