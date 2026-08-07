<?php

declare(strict_types=1);

namespace Pest\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Block;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector as BaseAbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;

/**
 * @phpstan-type StmtsAwareNode Block|Closure|Case_|Catch_|ClassMethod|Declare_|Do_|Else_|ElseIf_|Finally_|For_|Foreach_|Function_|If_|Namespace_|TryCatch|While_|FileNode
 */
abstract class AbstractRector extends BaseAbstractRector implements DocumentedRuleInterface
{
    protected function isExpectChain(MethodCall $methodCall): bool
    {
        $root = $this->getExpectChainRoot($methodCall);

        if ($root instanceof FuncCall) {
            return $this->isName($root, 'expect');
        }

        return false;
    }

    protected function getExpectFuncCall(MethodCall $methodCall): ?FuncCall
    {
        $root = $this->getExpectChainRoot($methodCall);

        if ($root instanceof FuncCall && $this->isName($root, 'expect')) {
            return $root;
        }

        if ($root instanceof PropertyFetch && $root->var instanceof FuncCall && $this->isName($root->var, 'expect')) {
            return $root->var;
        }

        return null;
    }

    protected function getExpectArgument(MethodCall $methodCall): ?Expr
    {
        $expectCall = $this->getExpectFuncCall($methodCall);

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

        return $arg->value;
    }

    protected function getMatcherSubjectHolder(MethodCall $methodCall): FuncCall|MethodCall|null
    {
        $current = $methodCall->var;

        while (true) {
            if ($current instanceof MethodCall) {
                if ($this->isName($current->name, 'and')) {
                    return $current;
                }

                $current = $current->var;

                continue;
            }

            if ($current instanceof PropertyFetch) {
                $current = $current->var;

                continue;
            }

            if ($current instanceof FuncCall && $this->isName($current, 'expect')) {
                return $current;
            }

            return null;
        }
    }

    protected function getMatcherSubject(MethodCall $methodCall): ?Expr
    {
        $holder = $this->getMatcherSubjectHolder($methodCall);

        if (! $holder instanceof FuncCall && ! $holder instanceof MethodCall) {
            return null;
        }

        if (! isset($holder->args[0]) || ! $holder->args[0] instanceof Arg) {
            return null;
        }

        return $holder->args[0]->value;
    }

    protected function isMatcherAppliedDirectly(MethodCall $methodCall): bool
    {
        $holder = $this->getMatcherSubjectHolder($methodCall);

        if (! $holder instanceof FuncCall && ! $holder instanceof MethodCall) {
            return false;
        }

        $var = $methodCall->var;

        if ($var instanceof PropertyFetch && $this->isName($var->name, 'not')) {
            $var = $var->var;
        }

        return $var === $holder;
    }

    protected function setMatcherSubject(MethodCall $methodCall, Expr $subject): bool
    {
        $holder = $this->getMatcherSubjectHolder($methodCall);

        if (! $holder instanceof FuncCall && ! $holder instanceof MethodCall) {
            return false;
        }

        $holder->args = [new Arg($subject)];

        return true;
    }

    protected function isExpectValueOfType(MethodCall $methodCall, string $typeCheck): bool
    {
        $subject = $this->getMatcherSubject($methodCall);
        if (! $subject instanceof Expr) {
            return false;
        }

        $valueType = $this->getType($subject);

        return match ($typeCheck) {
            'boolean' => ! $valueType->isBoolean()->no(),
            'string' => ! $valueType->isString()->no(),
            'integer' => ! $valueType->isInteger()->no(),
            'float' => ! $valueType->isFloat()->no(),
            'numeric' => ! $valueType->isInteger()->no() || ! $valueType->isFloat()->no(),
            'array' => ! $valueType->isArray()->no(),
            'object' => ! $valueType->isObject()->no(),
            'iterable' => ! $valueType->isIterable()->no(),
            'null' => ! $valueType->isNull()->no(),
            'scalar' => ! $valueType->isScalar()->no(),
            'callable' => ! $valueType->isCallable()->no(),
            default => false,
        };
    }

    protected function getExpectChainRoot(MethodCall $methodCall): FuncCall|PropertyFetch|null
    {
        $current = $methodCall->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        $search = $current;
        while ($search instanceof PropertyFetch || $search instanceof MethodCall) {
            $search = $search->var;
        }

        if ($search instanceof FuncCall) {
            return $search;
        }

        if ($current instanceof PropertyFetch && $current->var instanceof FuncCall) {
            return $current->var;
        }

        if ($current instanceof FuncCall) {
            return $current;
        }

        if ($current instanceof PropertyFetch) {
            return $current;
        }

        return null;
    }

    protected function hasNotModifier(MethodCall $methodCall): bool
    {
        $current = $methodCall->var;

        return $current instanceof PropertyFetch && $this->isName($current, 'not');
    }

    /**
     * @return array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>, is_property?: bool}>
     */
    protected function collectChainMethods(MethodCall $methodCall): array
    {
        $methods = [];
        $current = $methodCall;

        while (true) {
            if ($current instanceof MethodCall) {
                $methods[] = [
                    'name' => $current->name,
                    'args' => $current->args,
                    'is_property' => false,
                ];

                $next = $current->var;
            } elseif ($current instanceof PropertyFetch) {
                $methods[] = [
                    'name' => $current->name,
                    'args' => [],
                    'is_property' => true,
                ];

                $next = $current->var;
            } else {
                break;
            }

            if ($next instanceof FuncCall) {
                break;
            }

            $current = $next;
        }

        return array_reverse($methods);
    }

    /**
     * @param  array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>, is_property?: bool}>  $methods
     */
    protected function rebuildMethodChain(Expr $base, array $methods): Expr
    {
        $result = $base;

        foreach ($methods as $method) {
            if (! empty($method['is_property'])) {
                $result = new PropertyFetch($result, $method['name']);
            } else {
                $result = new MethodCall($result, $method['name'], $method['args']);
            }
        }

        return $result;
    }

    protected function applyNewlineAttributes(Expr $chain): void
    {
        if (! defined(AttributeKey::class.'::NEWLINE_ON_FLUENT_CALL')) {
            return;
        }

        $current = $chain;

        while ($current instanceof MethodCall) {
            $var = $current->var;

            if ($var instanceof FuncCall) {
                break;
            }

            if ($var instanceof PropertyFetch) {
                $current = $var->var;

                continue;
            }

            if (! $var instanceof MethodCall) {
                break;
            }

            if ($this->isName($var->name, 'and')) {
                $var->setAttribute(AttributeKey::NEWLINE_ON_FLUENT_CALL, true);
                $current = $var->var;

                continue;
            }

            $current->setAttribute(AttributeKey::NEWLINE_ON_FLUENT_CALL, true);
            $current = $var;
        }
    }

    /**
     * @param  array<Node>  $sources
     */
    protected function copyComments(array $sources, Node $target): void
    {
        $comments = (array) $target->getAttribute('comments', []);

        foreach ($sources as $source) {
            $comments = array_merge($comments, (array) $source->getAttribute('comments', []));
        }

        if ($comments !== []) {
            $target->setAttribute('comments', $comments);
        }
    }

    /**
     * @return array<Node\Stmt>|null
     */
    protected function getStatements(Node $node): ?array
    {
        if (! NodeGroup::isStmtAwareNode($node)) {
            return null;
        }

        /** @var StmtsAwareNode $node */
        return $node->stmts;
    }

    /**
     * @param  array<Node\Stmt>  $stmts
     */
    protected function setStatements(Node $node, array $stmts): void
    {
        if (! NodeGroup::isStmtAwareNode($node)) {
            return;
        }

        /** @var StmtsAwareNode $node */
        $node->stmts = $stmts;
    }
}
