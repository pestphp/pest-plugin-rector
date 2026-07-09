<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeSlugRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts Str::slug() equality checks to toBeSlug() matcher (requires illuminate/support)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(Str::slug($value) === $value)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeSlug();
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

        if ($this->isStrSlug($expectArg->left)) {
            $staticCall = $expectArg->left;
            if ($this->nodeComparator->areNodesEqual($this->getFirstStaticArg($staticCall), $expectArg->right)) {
                if ($this->getType($expectArg->right)->isString()->no()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->right)];
                $node->name = new Identifier('toBeSlug');

                return $node;
            }
        }

        if ($this->isStrSlug($expectArg->right)) {
            $staticCall = $expectArg->right;
            if ($this->nodeComparator->areNodesEqual($expectArg->left, $this->getFirstStaticArg($staticCall))) {
                if ($this->getType($expectArg->left)->isString()->no()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->left)];
                $node->name = new Identifier('toBeSlug');

                return $node;
            }
        }

        return null;
    }

    private function isStrSlug(?Node $node): bool
    {
        return $node instanceof StaticCall
            && $this->isName($node->class, 'Illuminate\Support\Str')
            && $this->isName($node->name, 'slug');
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
