<?php

declare(strict_types=1);

namespace RectorPest\Analyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

final class PestChainAnalyzer
{
    /** @var list<string> */
    private const PEST_TEST_FUNCTIONS = ['it', 'test', 'todo'];

    public static function hasMethodNamedBetween(MethodCall $outerMethodCall, MethodCall $innerMethodCall, string $methodName): bool
    {
        $current = $outerMethodCall;

        while ($current !== $innerMethodCall && $current->var instanceof MethodCall) {
            if ($current->name instanceof Identifier && $current->name->toString() === $methodName) {
                return true;
            }

            $current = $current->var;
        }

        return false;
    }

    public static function getRootFuncCall(MethodCall $methodCall): ?FuncCall
    {
        $current = $methodCall->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        while ($current instanceof PropertyFetch) {
            $current = $current->var;
        }

        return $current instanceof FuncCall ? $current : null;
    }

    public static function isPestTestChain(MethodCall $methodCall): bool
    {
        $root = self::getRootFuncCall($methodCall);

        if (! $root instanceof FuncCall || ! $root->name instanceof Name) {
            return false;
        }

        return in_array($root->name->toString(), self::PEST_TEST_FUNCTIONS, true);
    }

    public static function isExpectChain(MethodCall $methodCall): bool
    {
        $root = self::getRootFuncCall($methodCall);

        return $root instanceof FuncCall && $root->name instanceof Name && $root->name->toString() === 'expect';
    }

    public static function isDirectExpectMethod(MethodCall $methodCall): bool
    {
        if ($methodCall->var instanceof FuncCall) {
            return $methodCall->var->name instanceof Name && $methodCall->var->name->toString() === 'expect';
        }

        return $methodCall->var instanceof PropertyFetch
            && $methodCall->var->name instanceof Identifier
            && $methodCall->var->name->toString() === 'not'
            && $methodCall->var->var instanceof FuncCall
            && $methodCall->var->var->name instanceof Name
            && $methodCall->var->var->name->toString() === 'expect';
    }

    public static function getExpectArgument(MethodCall $methodCall): ?Expr
    {
        if (! self::isExpectChain($methodCall)) {
            return null;
        }

        $root = self::getRootFuncCall($methodCall);
        $arg = $root?->args[0] ?? null;

        return $arg instanceof Arg ? $arg->value : null;
    }

    public static function hasNotModifier(MethodCall $methodCall): bool
    {
        return $methodCall->var instanceof PropertyFetch
            && $methodCall->var->name instanceof Identifier
            && $methodCall->var->name->toString() === 'not';
    }
}
