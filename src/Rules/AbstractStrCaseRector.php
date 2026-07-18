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

abstract class AbstractStrCaseRector extends AbstractRector
{
    abstract protected function getStrMethodName(): string;

    abstract protected function getMatcherName(): string;

    /**
     * @return array<class-string<Node>>
     */
    final public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param  MethodCall  $node
     */
    final public function refactor(Node $node): ?Node
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

        if ($this->isStrMethod($expectArg->left)) {
            $staticCall = $expectArg->left;
            if ($this->nodeComparator->areNodesEqual($this->getFirstStaticArg($staticCall), $expectArg->right)) {
                if ($this->getType($expectArg->right)->isString()->no()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->right)];
                $node->name = new Identifier($this->getMatcherName());

                return $node;
            }
        }

        if ($this->isStrMethod($expectArg->right)) {
            $staticCall = $expectArg->right;
            if ($this->nodeComparator->areNodesEqual($expectArg->left, $this->getFirstStaticArg($staticCall))) {
                if ($this->getType($expectArg->left)->isString()->no()) {
                    return null;
                }

                $expectCall->args = [new Arg($expectArg->left)];
                $node->name = new Identifier($this->getMatcherName());

                return $node;
            }
        }

        return null;
    }

    private function isStrMethod(?Node $node): bool
    {
        return $node instanceof StaticCall
            && $this->isName($node->class, 'Illuminate\Support\Str')
            && $this->isName($node->name, $this->getStrMethodName());
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
