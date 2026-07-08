<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts uppercase string checks to toBeUppercase() matcher
 */
final class UseToBeUppercaseRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts strtoupper() equality checks to toBeUppercase() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(strtoupper($value) === $value)->toBeTrue();
expect($value === strtoupper($value))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeUppercase();
expect($value)->toBeUppercase();
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

        // Pattern 1: strtoupper($value) === $value
        if ($this->isStrtoupper($expectArg->left)) {
            $strtoUpperCall = $expectArg->left;
            if ($this->nodeComparator->areNodesEqual($this->getFirstArg($strtoUpperCall), $expectArg->right)) {
                if ($this->getType($expectArg->right)->isString()->no()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->right)];
                $node->name = new Identifier('toBeUppercase');
                return $node;
            }
        }

        // Pattern 2: $value === strtoupper($value)
        if ($this->isStrtoupper($expectArg->right)) {
            $strtoUpperCall = $expectArg->right;
            if ($this->nodeComparator->areNodesEqual($expectArg->left, $this->getFirstArg($strtoUpperCall))) {
                if ($this->getType($expectArg->left)->isString()->no()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->left)];
                $node->name = new Identifier('toBeUppercase');
                return $node;
            }
        }

        return null;
    }

    private function isStrtoupper(?Node $node): bool
    {
        return $node instanceof FuncCall && $this->isName($node, 'strtoupper');
    }

    private function getFirstArg(?Node $node): ?Node
    {
        if (! $node instanceof FuncCall) {
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
