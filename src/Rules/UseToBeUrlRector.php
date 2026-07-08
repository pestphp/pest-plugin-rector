<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use RuntimeException;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts filter_var URL validation to toBeUrl() matcher
 */
final class UseToBeUrlRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts filter_var($url, FILTER_VALIDATE_URL) checks to toBeUrl() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(filter_var($url, FILTER_VALIDATE_URL))->not->toBeFalse();
expect(filter_var($url, FILTER_VALIDATE_URL) !== false)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($url)->toBeUrl();
expect($url)->toBeUrl();
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

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);

        // Pattern 1: expect(filter_var($url, FILTER_VALIDATE_URL))->not->toBeFalse()
        if ($this->isName($node->name, 'toBeFalse') && $this->isNotChain($node) && $this->isFilterVarValidateUrl($expectArg)) {
            $urlArg = $this->getUrlFromFilterVar($expectArg);
            if (!$urlArg instanceof Expr) {
                return null;
            }

            if ($this->getType($urlArg)->isString()->no()) {
                return null;
            }

            $expectCall->args = [new Arg($urlArg)];
            // Remove the 'not' from the chain
            $node->var = $this->removeNotFromChain($node->var);
            $node->name = new Identifier('toBeUrl');
            return $node;
        }

        // Pattern 2: expect(filter_var($url, FILTER_VALIDATE_URL) !== false)->toBeTrue()
        if ($this->isName($node->name, 'toBeTrue') && $expectArg instanceof NotIdentical && ($this->isFilterVarValidateUrl($expectArg->left) && $this->isFalse($expectArg->right))) {
            $urlArg = $this->getUrlFromFilterVar($expectArg->left);
            if (!$urlArg instanceof Expr) {
                return null;
            }

            if ($this->getType($urlArg)->isString()->no()) {
                return null;
            }

            $expectCall->args = [new Arg($urlArg)];
            $node->name = new Identifier('toBeUrl');
            return $node;
        }

        return null;
    }

    private function isFilterVarValidateUrl(?Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $this->isName($node, 'filter_var')) {
            return false;
        }

        if (count($node->args) < 2) {
            return false;
        }

        $filterArg = $node->args[1];
        if (! $filterArg instanceof Arg) {
            return false;
        }

        if ($filterArg->value instanceof ConstFetch) {
            return $this->isName($filterArg->value, 'FILTER_VALIDATE_URL');
        }

        return false;
    }

    private function getUrlFromFilterVar(?Node $node): ?Expr
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

    private function isFalse(?Node $node): bool
    {
        if (! $node instanceof ConstFetch) {
            return false;
        }

        return $this->isName($node, 'false');
    }

    private function isNotChain(MethodCall $methodCall): bool
    {
        $var = $methodCall->var;

        while ($var instanceof PropertyFetch) {
            if ($this->isName($var->name, 'not')) {
                return true;
            }

            $var = $var->var;
        }

        return false;
    }

    private function removeNotFromChain(Node $node): Expr
    {
        if ($node instanceof PropertyFetch && $this->isName($node->name, 'not')) {
            return $node->var;
        }

        if ($node instanceof Expr) {
            return $node;
        }

        throw new RuntimeException('Node is not an Expr');
    }
}
