<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts ctype_alnum() checks to toBeAlphaNumeric() matcher
 */
final class UseToBeAlphaNumericRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts ctype_alnum() checks to toBeAlphaNumeric() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(ctype_alnum($value))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeAlphaNumeric();
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
        if (! $expectArg instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($expectArg, 'ctype_alnum')) {
            return null;
        }

        if (count($expectArg->args) !== 1) {
            return null;
        }

        $ctypeArg = $expectArg->args[0];
        if (! $ctypeArg instanceof Arg) {
            return null;
        }

        if ($this->getType($ctypeArg->value)->isString()->no()) {
            return null;
        }

        // Replace expect(ctype_alnum($value)) with expect($value)
        $expectCall->args = [new Arg($ctypeArg->value)];

        // Replace toBeTrue() with toBeAlphaNumeric()
        $node->name = new Identifier('toBeAlphaNumeric');

        return $node;
    }
}
