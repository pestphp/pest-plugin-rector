<?php

declare(strict_types=1);

namespace RectorPest\Analyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use RectorPest\ValueObject\ExpectationSemanticAnalysis;

final class SemanticExpectationAnalyzer
{
    /** @var array<string, string> */
    private const TYPE_MATCHER_CATEGORIES = [
        'toBeArray' => 'array',
        'toBeBool' => 'bool',
        'toBeCallable' => 'callable',
        'toBeFloat' => 'float',
        'toBeInt' => 'int',
        'toBeIterable' => 'iterable',
        'toBeNull' => 'null',
        'toBeNumeric' => 'numeric',
        'toBeObject' => 'object',
        'toBeScalar' => 'scalar',
        'toBeString' => 'string',
    ];

    public static function analyzeLiteralTypeMatcher(MethodCall $methodCall): ?ExpectationSemanticAnalysis
    {
        if (! PestChainAnalyzer::isDirectExpectMethod($methodCall) || ! $methodCall->name instanceof Identifier) {
            return null;
        }

        $matcher = $methodCall->name->toString();
        $expectedCategory = self::matcherCategory($matcher);

        if ($expectedCategory === null) {
            return null;
        }

        $expectArg = PestChainAnalyzer::getExpectArgument($methodCall);

        if (! $expectArg instanceof Expr) {
            return null;
        }

        $literalCategory = self::deterministicLiteralCategory($expectArg);

        if ($literalCategory === null) {
            return null;
        }

        $matches = self::matchesLiteralCategory($expectArg, $expectedCategory);

        if ($matches === null) {
            return null;
        }

        return ExpectationSemanticAnalysis::forDeterministicLiteralTypeCheck(
            $matcher,
            $expectedCategory,
            $literalCategory,
            PestChainAnalyzer::hasNotModifier($methodCall),
            $matches,
        );
    }

    public static function matcherCategory(string $matcher): ?string
    {
        return self::TYPE_MATCHER_CATEGORIES[$matcher] ?? null;
    }

    public static function deterministicLiteralCategory(Expr $expr): ?string
    {
        return match (true) {
            $expr instanceof String_ => 'string',
            $expr instanceof Int_ => 'int',
            $expr instanceof Float_ => 'float',
            $expr instanceof Array_ => 'array',
            $expr instanceof Closure, $expr instanceof ArrowFunction => 'callable',
            $expr instanceof New_ => 'object',
            $expr instanceof ClassConstFetch => $expr->name instanceof Identifier && $expr->name->toString() === 'class' ? 'string' : null,
            $expr instanceof ConstFetch => self::literalCategoryFromConstFetch($expr),
            default => null,
        };
    }

    private static function matchesLiteralCategory(Expr $expr, string $category): ?bool
    {
        return match (true) {
            $expr instanceof String_ => match ($category) {
                'string', 'scalar' => true,
                'numeric' => is_numeric($expr->value),
                default => false,
            },
            $expr instanceof Int_ => in_array($category, ['int', 'numeric', 'scalar'], true),
            $expr instanceof Float_ => in_array($category, ['float', 'numeric', 'scalar'], true),
            $expr instanceof Array_ => in_array($category, ['array', 'iterable'], true),
            $expr instanceof Closure, $expr instanceof ArrowFunction => in_array($category, ['callable', 'object'], true),
            $expr instanceof New_ => $category === 'object',
            $expr instanceof ClassConstFetch => in_array($category, ['string', 'scalar'], true) && $expr->name instanceof Identifier && $expr->name->toString() === 'class',
            $expr instanceof ConstFetch => self::matchesConstFetch($expr, $category),
            default => null,
        };
    }

    private static function literalCategoryFromConstFetch(ConstFetch $expr): ?string
    {
        return match ($expr->name->toLowerString()) {
            'true', 'false' => 'bool',
            'null' => 'null',
            default => null,
        };
    }

    private static function matchesConstFetch(ConstFetch $expr, string $category): ?bool
    {
        $name = $expr->name->toLowerString();

        return match ($name) {
            'true', 'false' => in_array($category, ['bool', 'scalar'], true),
            'null' => $category === 'null',
            default => null,
        };
    }
}
