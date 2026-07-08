<?php

declare(strict_types=1);

namespace RectorPest;

use Pest\Expectation;
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
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector as BaseAbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;

/**
 * Base abstract class for all Pest rectors
 * Provides common helper methods for working with Pest's expect() chains
 *
 * @phpstan-type StmtsAwareNode Block|Closure|Case_|Catch_|ClassMethod|Declare_|Do_|Else_|ElseIf_|Finally_|For_|Foreach_|Function_|If_|Namespace_|TryCatch|While_|FileNode
 */
abstract class AbstractRector extends BaseAbstractRector implements DocumentedRuleInterface
{
    /**
     * Check if a method call is part of an expect() chain
     */
    protected function isExpectChain(MethodCall $methodCall): bool
    {
        $root = $this->getExpectChainRoot($methodCall);

        if ($root instanceof FuncCall) {
            return $this->isName($root, 'expect');
        }

        return false;
    }

    /**
     * Get the root expect() function call from a method chain
     */
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

    /**
     * Get the argument passed to expect() from a method chain
     */
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

    /**
     * Check if the expect() argument matches a specific type category.
     * Unwraps the Expectation<T> generic (resolved by PestStan's PHPStan extension)
     * to extract T, then checks the type against the given category.
     */
    protected function isExpectValueOfType(MethodCall $methodCall, string $typeCheck): bool
    {
        $expectCall = $this->getExpectFuncCall($methodCall);
        if (! $expectCall instanceof FuncCall) {
            return false;
        }

        $valueType = $this->getType($expectCall)->getTemplateType(Expectation::class, 'TValue');

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

    /**
     * Get the root of an expect chain (either FuncCall or PropertyFetch for ->not)
     */
    protected function getExpectChainRoot(MethodCall $methodCall): FuncCall|PropertyFetch|null
    {
        $current = $methodCall->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        // Try to find an underlying FuncCall (expect(...)) even if there are
        // intermediate PropertyFetch nodes (e.g. ->not) whose var is a
        // MethodCall. Walk down through property/method var links to locate
        // the FuncCall if present.
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

    /**
     * Check if the expect chain has a ->not modifier
     */
    protected function hasNotModifier(MethodCall $methodCall): bool
    {
        $current = $methodCall->var;

        // Check if the immediate predecessor is a ->not property fetch
        return $current instanceof PropertyFetch && $this->isName($current, 'not');
    }

    /**
     * Collect all method calls in a chain from root to leaf
     *
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
     * Rebuild a method chain from a base expression
     *
     * @param array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>, is_property?: bool}> $methods
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
     * @param array<Node\Stmt> $stmts
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
