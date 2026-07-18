<?php

declare(strict_types=1);

namespace Pest\Rector\Rules\Pest2ToPest3;

use Pest\Rector\AbstractRector;
use Pest\Rector\Analyzer\PestChainAnalyzer;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Reflection\ReflectionProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UsesToExtendRector extends AbstractRector
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider
    ) {}

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts uses() and pest()->uses() to pest()->extend() for classes and pest()->use() for traits',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
uses(Tests\TestCase::class)->in('Feature');
uses(Illuminate\Foundation\Testing\RefreshDatabase::class);
pest()->uses(Tests\TestCase::class)->in('Feature');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
pest()->extend(Tests\TestCase::class)->in('Feature');
pest()->use(Illuminate\Foundation\Testing\RefreshDatabase::class);
pest()->extend(Tests\TestCase::class)->in('Feature');
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
        return [MethodCall::class, FuncCall::class];
    }

    /**
     * @param  MethodCall|FuncCall  $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof FuncCall) {
            return $this->refactorFuncCall($node);
        }

        return $this->refactorMethodCall($node);
    }

    private function refactorFuncCall(FuncCall $node): ?Node
    {
        if (! $this->isName($node, 'uses')) {
            return null;
        }

        if ($node->args === []) {
            return null;
        }

        return $this->transformUsesArgs($node->args, []);
    }

    private function refactorMethodCall(MethodCall $node): ?Node
    {
        $usesFuncCall = $this->findUsesFuncCallInChain($node);
        if ($usesFuncCall instanceof FuncCall) {
            $methodsAfter = $this->collectMethodsFromFuncCall($node);

            return $this->transformUsesArgs($usesFuncCall->args, $methodsAfter);
        }

        $usesCall = $this->findUsesCallInChain($node);
        if (! $usesCall instanceof MethodCall) {
            return null;
        }

        if (! $this->isDirectUsesCall($node) && ! $this->hasUsesInChain($node)) {
            return null;
        }

        if ($this->isName($node->name, 'uses') && $this->isPestChain($node)) {
            return $this->transformPestUsesCall($node, []);
        }

        if ($this->hasUsesInChain($node)) {
            $methodsAfter = $this->collectMethodsUntilUses($node);

            return $this->transformPestUsesCall($usesCall, $methodsAfter);
        }

        return null;
    }

    private function findUsesFuncCallInChain(MethodCall $node): ?FuncCall
    {
        $current = $node->var;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        if ($current instanceof FuncCall && $this->isName($current, 'uses')) {
            return $current;
        }

        return null;
    }

    /**
     * @return array<array{name: Identifier|Expr, args: array<Arg|VariadicPlaceholder>}>
     */
    private function collectMethodsFromFuncCall(MethodCall $outermost): array
    {
        $methods = [];
        $current = $outermost;

        while ($current instanceof MethodCall) {
            $methods[] = [
                'name' => $current->name,
                'args' => $current->args,
            ];
            $current = $current->var instanceof MethodCall ? $current->var : null;
        }

        return array_reverse($methods);
    }

    /**
     * @param  array<Arg|VariadicPlaceholder>  $args
     * @param  array<array{name: Identifier|Expr, args: array<Arg|VariadicPlaceholder>}>  $methodsAfter
     */
    private function transformUsesArgs(array $args, array $methodsAfter): ?Node
    {
        if ($args === []) {
            return null;
        }

        [$classes, $traits] = $this->separateClassesAndTraits($args);

        if ($classes === [] && $traits === []) {
            return null;
        }

        $pestCall = new FuncCall(new Name('pest'));

        $result = $pestCall;

        if ($classes !== []) {
            $result = new MethodCall($result, 'extend', $classes);
        }

        if ($traits !== []) {
            $result = new MethodCall($result, 'use', $traits);
        }

        foreach ($methodsAfter as $method) {
            $result = new MethodCall($result, $method['name'], $method['args']);
        }

        return $result;
    }

    private function isDirectUsesCall(MethodCall $node): bool
    {
        return $this->isName($node->name, 'uses') && $this->isPestChain($node);
    }

    private function hasUsesInChain(MethodCall $node): bool
    {
        $current = $node->var;

        while ($current instanceof MethodCall) {
            if ($this->isName($current->name, 'uses') && $this->isPestChain($current)) {
                return true;
            }

            $current = $current->var;
        }

        return false;
    }

    private function findUsesCallInChain(MethodCall $node): ?MethodCall
    {
        if ($this->isName($node->name, 'uses') && $this->isPestChain($node)) {
            return $node;
        }

        $current = $node->var;

        while ($current instanceof MethodCall) {
            if ($this->isName($current->name, 'uses') && $this->isPestChain($current)) {
                return $current;
            }

            $current = $current->var;
        }

        return null;
    }

    /**
     * @return array<array{name: Identifier|Expr, args: array<Arg|VariadicPlaceholder>}>
     */
    private function collectMethodsUntilUses(MethodCall $outermost): array
    {
        $methods = [];
        $current = $outermost;

        while ($current instanceof MethodCall) {
            if ($this->isName($current->name, 'uses')) {
                break;
            }

            $methods[] = [
                'name' => $current->name,
                'args' => $current->args,
            ];
            $current = $current->var instanceof MethodCall ? $current->var : null;
        }

        return array_reverse($methods);
    }

    /**
     * @param  array<array{name: Identifier|Expr, args: array<Arg|VariadicPlaceholder>}>  $methodsAfter
     */
    private function transformPestUsesCall(MethodCall $usesCall, array $methodsAfter): ?Node
    {
        if ($usesCall->args === []) {
            return null;
        }

        [$classes, $traits] = $this->separateClassesAndTraits($usesCall->args);

        if ($classes === [] && $traits === []) {
            return null;
        }

        $pestCall = $this->getPestFuncCall($usesCall);
        if (! $pestCall instanceof FuncCall) {
            return null;
        }

        $result = $pestCall;

        if ($classes !== []) {
            $result = new MethodCall($result, 'extend', $classes);
        }

        if ($traits !== []) {
            $result = new MethodCall($result, 'use', $traits);
        }

        foreach ($methodsAfter as $method) {
            $result = new MethodCall($result, $method['name'], $method['args']);
        }

        return $result;
    }

    /**
     * @param  array<Arg|VariadicPlaceholder>  $args
     * @return array{0: array<Arg>, 1: array<Arg>}
     */
    private function separateClassesAndTraits(array $args): array
    {
        $classes = [];
        $traits = [];

        foreach ($args as $arg) {
            if (! $arg instanceof Arg) {
                continue;
            }

            $classConstFetch = $arg->value;
            if (! $classConstFetch instanceof ClassConstFetch) {
                continue;
            }

            if (! $classConstFetch->class instanceof Name) {
                continue;
            }

            $className = $classConstFetch->class->toString();

            if ($this->reflectionProvider->hasClass($className)) {
                $classReflection = $this->reflectionProvider->getClass($className);

                if ($classReflection->isTrait()) {
                    $traits[] = new Arg($classConstFetch);
                } else {
                    $classes[] = new Arg($classConstFetch);
                }
            } else {
                $classes[] = new Arg($classConstFetch);
            }
        }

        return [$classes, $traits];
    }

    private function isPestChain(MethodCall $methodCall): bool
    {
        return $this->getPestFuncCall($methodCall) instanceof FuncCall;
    }

    private function getPestFuncCall(MethodCall $methodCall): ?FuncCall
    {
        $root = PestChainAnalyzer::getRootFuncCall($methodCall);

        if ($root instanceof FuncCall && $this->isName($root, 'pest')) {
            return $root;
        }

        return null;
    }
}
