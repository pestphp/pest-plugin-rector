<?php

declare(strict_types=1);

namespace RectorPest\Concerns;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

trait ExpectChainValidation
{
    /**
     * @param  array<string>  $targetFunctions  List of function names to match (e.g., ['is_file', 'is_dir'])
     * @param  array<string>  $validMethods  Matcher methods to look for (default: toBeTrue, toBeFalse)
     * @return array{expectCall: FuncCall, funcCall: FuncCall, methodName: string}|null
     */
    protected function extractFunctionFromExpect(
        MethodCall $node,
        array $targetFunctions,
        array $validMethods = ['toBeTrue', 'toBeFalse']
    ): ?array {
        if (! $this->isExpectChain($node)) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        $methodName = $node->name->name;

        if (! in_array($methodName, $validMethods, true)) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
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

        if (! $arg->value instanceof FuncCall) {
            return null;
        }

        $funcCall = $arg->value;
        if (! $funcCall->name instanceof Name) {
            return null;
        }

        $funcName = $funcCall->name->toString();
        if (! in_array($funcName, $targetFunctions, true)) {
            return null;
        }

        return [
            'expectCall' => $expectCall,
            'funcCall' => $funcCall,
            'methodName' => $methodName,
        ];
    }

    protected function calculateNeedsNot(string $methodName, MethodCall $node): bool
    {
        $needsNot = $methodName === 'toBeFalse';
        if ($this->hasNotModifier($node)) {
            return ! $needsNot;
        }

        return $needsNot;
    }

    /**
     * @param  array<Arg>  $matcherArgs  Arguments for the matcher method
     */
    protected function buildMatcherCall(
        FuncCall $expectCall,
        Expr $newExpectValue,
        string $matcherMethod,
        array $matcherArgs,
        bool $needsNot
    ): MethodCall {
        $expectCall->args[0] = new Arg($newExpectValue);

        if ($needsNot) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, $matcherMethod, $matcherArgs);
        }

        return new MethodCall($expectCall, $matcherMethod, $matcherArgs);
    }

    protected function isFalse(Node $node): bool
    {
        return $node instanceof ConstFetch && $this->isName($node, 'false');
    }
}
