<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts instanceof checks to toBeInstanceOf() matcher
 */
final class UseInstanceOfMatcherRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect($obj instanceof User)->toBeTrue() to expect($obj)->toBeInstanceOf(User::class)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($user instanceof User)->toBeTrue();
expect($object instanceof DateTime)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($user)->toBeInstanceOf(User::class);
expect($object)->toBeInstanceOf(DateTime::class);
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
        if (! $expectArg instanceof Instanceof_) {
            return null;
        }

        $object = $expectArg->expr;
        $class = $expectArg->class;

        if (! $class instanceof Name) {
            return null;
        }

        $expectCall->args = [new Arg($object)];

        $classConstFetch = new ClassConstFetch($class, new Identifier('class'));
        $node->name = new Identifier('toBeInstanceOf');
        $node->args = [new Arg($classConstFetch)];

        return $node;
    }
}
