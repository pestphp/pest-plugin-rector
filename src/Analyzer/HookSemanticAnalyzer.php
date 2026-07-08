<?php

declare(strict_types=1);

namespace RectorPest\Analyzer;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use RectorPest\Support\PestFunctionDetector;

final class HookSemanticAnalyzer
{
    /**
     * @param array<string, string> $hookReplacements
     * @return list<FuncCall>
     */
    public static function findInvalidDescribeHooks(Closure $closure, array $hookReplacements): array
    {
        $invalidHooks = [];

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof FuncCall) {
                continue;
            }

            $call = $stmt->expr;
            $name = PestFunctionDetector::getFunctionName($call);

            if ($name !== null && isset($hookReplacements[$name])) {
                $invalidHooks[] = $call;

                continue;
            }

            if (! PestFunctionDetector::isDescribeFunction($call)) {
                continue;
            }

            $nestedClosure = PestFunctionDetector::extractClosure($call);

            if (! $nestedClosure instanceof Closure) {
                continue;
            }

            array_push($invalidHooks, ...self::findInvalidDescribeHooks($nestedClosure, $hookReplacements));
        }

        return $invalidHooks;
    }
}
