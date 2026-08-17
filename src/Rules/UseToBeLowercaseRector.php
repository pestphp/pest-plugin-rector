<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeLowercaseRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts strtolower() equality checks to toBeLowercase() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(strtolower($value) === $value)->toBeTrue();
expect($value === strtolower($value))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeLowercase();
expect($value)->toBeLowercase();
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

        $expectCall = $this->getMatcherSubjectHolder($node);
        if (! $expectCall instanceof FuncCall && ! $expectCall instanceof MethodCall) {
            return null;
        }

        $expectArg = $this->getMatcherSubject($node);
        if (! $expectArg instanceof Identical) {
            return null;
        }

        if ($this->isStrtolower($expectArg->left)) {
            $strtoLowerCall = $expectArg->left;
            if ($this->nodeComparator->areNodesEqual($this->getFirstArg($strtoLowerCall), $expectArg->right)) {
                if (! $this->getType($expectArg->right)->isString()->yes()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->right)];
                $node->name = new Identifier('toBeLowercase');

                return $node;
            }
        }

        if ($this->isStrtolower($expectArg->right)) {
            $strtoLowerCall = $expectArg->right;
            if ($this->nodeComparator->areNodesEqual($expectArg->left, $this->getFirstArg($strtoLowerCall))) {
                if (! $this->getType($expectArg->left)->isString()->yes()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->left)];
                $node->name = new Identifier('toBeLowercase');

                return $node;
            }
        }

        return null;
    }

    private function isStrtolower(?Node $node): bool
    {
        return $node instanceof FuncCall && $this->isName($node, 'strtolower');
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
