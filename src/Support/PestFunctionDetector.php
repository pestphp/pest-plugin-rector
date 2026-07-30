<?php

declare(strict_types=1);

namespace Pest\Rector\Support;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

final class PestFunctionDetector
{
    /** @var list<string> */
    private const array ALL_FUNCTIONS = [
        'it',
        'test',
        'todo',
        'describe',
        'beforeEach',
        'afterEach',
        'beforeAll',
        'afterAll',
    ];

    /** @var array<string, int> */
    private const array CLOSURE_FUNCTIONS = [
        'it' => 1,
        'test' => 1,
        'describe' => 1,
        'beforeEach' => 0,
        'afterEach' => 0,
        'beforeAll' => 0,
        'afterAll' => 0,
    ];

    /** @var list<string> */
    private const array TEST_FUNCTIONS = ['it', 'test'];

    public static function getFunctionName(FuncCall $node): ?string
    {
        if (! $node->name instanceof Name) {
            return null;
        }

        $name = $node->name->toString();

        return in_array($name, self::ALL_FUNCTIONS, true) ? $name : null;
    }

    public static function isTestFunction(FuncCall $node): bool
    {
        $name = self::getFunctionName($node);

        return $name !== null && in_array($name, self::TEST_FUNCTIONS, true);
    }

    public static function isDescribeFunction(FuncCall $node): bool
    {
        return self::getFunctionName($node) === 'describe';
    }

    public static function extractClosure(FuncCall $node): Closure|ArrowFunction|null
    {
        $name = self::getFunctionName($node);
        if ($name === null) {
            return null;
        }

        $closureArgIndex = self::CLOSURE_FUNCTIONS[$name] ?? null;
        if ($closureArgIndex === null) {
            return null;
        }

        $args = $node->getArgs();

        if (! isset($args[$closureArgIndex])) {
            return null;
        }

        $value = $args[$closureArgIndex]->value;

        if ($value instanceof Closure || $value instanceof ArrowFunction) {
            return $value;
        }

        return null;
    }

    public static function extractDescription(FuncCall $node): ?string
    {
        if (! self::isTestFunction($node)) {
            return null;
        }

        $args = $node->getArgs();
        if ($args === []) {
            return null;
        }

        $firstArg = $args[0]->value;
        if ($firstArg instanceof String_) {
            return $firstArg->value;
        }

        return null;
    }

    public static function closureRequiresInstanceBinding(Closure|ArrowFunction $closure): bool
    {
        return array_any($closure->getSubNodeNames(), fn (string $subNodeName): bool => self::subNodeUsesThis($closure->{$subNodeName}));
    }

    private static function subNodeUsesThis(mixed $subNode): bool
    {
        if ($subNode instanceof Variable && $subNode->name === 'this') {
            return true;
        }

        if ($subNode instanceof FuncCall) {
            return self::funcCallUsesThis($subNode);
        }

        if ($subNode instanceof Closure) {
            return ! $subNode->static && self::closureRequiresInstanceBinding($subNode);
        }

        if ($subNode instanceof ArrowFunction) {
            return self::closureRequiresInstanceBinding($subNode);
        }

        if ($subNode instanceof Node) {
            return array_any($subNode->getSubNodeNames(), fn (string $subNodeName): bool => self::subNodeUsesThis($subNode->{$subNodeName}));
        }

        if (! is_array($subNode)) {
            return false;
        }

        return array_any($subNode, fn ($item): bool => self::subNodeUsesThis($item));
    }

    private static function funcCallUsesThis(FuncCall $funcCall): bool
    {
        $pestCallback = self::extractClosure($funcCall);

        foreach ($funcCall->args as $arg) {
            if (! $arg instanceof Arg) {
                continue;
            }

            if ($pestCallback !== null && $arg->value === $pestCallback) {
                continue;
            }

            if (self::subNodeUsesThis($arg)) {
                return true;
            }
        }

        return false;
    }
}
