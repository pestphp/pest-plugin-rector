<?php

declare(strict_types=1);

namespace RectorPest\Rules\Pest2ToPest3;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts toHaveMethod/toHaveMethods to work with class names instead of objects
 */
final class ToHaveMethodOnClassRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes expect($object)->toHaveMethod() to expect($object::class)->toHaveMethod() for Pest v3',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($user)->toHaveMethod('getName');
expect($user)->toHaveMethods(['getName', 'getEmail']);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($user::class)->toHaveMethod('getName');
expect($user::class)->toHaveMethods(['getName', 'getEmail']);
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

        if (! $this->isNames($node->name, ['toHaveMethod', 'toHaveMethods'])) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (!$expectArg instanceof Expr) {
            return null;
        }

        if ($expectArg instanceof ClassConstFetch) {
            return null;
        }

        $classConstFetch = new ClassConstFetch($expectArg, new Identifier('class'));

        $expectCall->args[0] = $this->nodeFactory->createArg($classConstFetch);

        return $node;
    }
}
