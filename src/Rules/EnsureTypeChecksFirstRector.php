<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\VariadicPlaceholder;
use Rector\PhpParser\Enum\NodeGroup;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class EnsureTypeChecksFirstRector extends AbstractRector
{
    /**
     * @var string[]
     */
    public static array $typeMatchers = [
        'toBeInt', 'toBeString', 'toBeArray', 'toBeFloat', 'toBeBool',
        'toBeNull', 'toBeInstanceOf', 'toBeNumeric', 'toBeIterable',
        'toBeCallable', 'toBeObject', 'toBeScalar', 'toBeResource',
    ];

    /**
     * @var string[]
     */
    public static array $prefixModifiers = ['not'];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Ensure type-check matchers (e.g. toBeInt, toBeInstanceOf) appear before value assertions in expect() chains and consecutive expects',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)->toBeInt();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBeInt()->toBe(10);
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($a)->toBeInt();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBeInt();
expect($a)->toBe(10);
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
        return NodeGroup::STMTS_AWARE;
    }

    public function refactor(Node $node): ?Node
    {
        $stmts = $this->getStatements($node);
        if ($stmts === null) {
            return null;
        }

        $hasChanged = false;

        foreach ($stmts as $key => $stmt) {
            if (! is_int($key)) {
                continue;
            }

            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof MethodCall) {
                continue;
            }

            $methodCall = $stmt->expr;
            if (! $this->isExpectChain($methodCall)) {
                continue;
            }

            $methods = $this->collectChainMethods($methodCall);
            if ($methods === []) {
                continue;
            }

            $newMethods = $this->reorderWithinAndSegments($methods);

            if ($newMethods !== $methods) {
                $root = $this->getExpectChainRoot($methodCall);
                if (! $root instanceof Expr) {
                    continue;
                }

                $stmt->expr = $this->rebuildMethodChain($root, $newMethods);
                $hasChanged = true;

                continue;
            }

            if (isset($stmts[$key + 1]) && $stmts[$key + 1] instanceof Expression) {
                $next = $stmts[$key + 1];
                if (! $next->expr instanceof MethodCall) {
                    continue;
                }

                $nextMethodCall = $next->expr;
                if ($this->isExpectChain($nextMethodCall) && ! $this->hasNotModifier($nextMethodCall)) {
                    $firstArg = $this->getExpectArgument($methodCall);
                    $secondArg = $this->getExpectArgument($nextMethodCall);
                    if ($firstArg instanceof Expr && $secondArg instanceof Expr && $this->nodeComparator->areNodesEqual($firstArg, $secondArg)) {
                        $firstPartition = $this->partitionTypeAndNonType($this->collectChainMethods($methodCall));
                        $secondPartition = $this->partitionTypeAndNonType($this->collectChainMethods($nextMethodCall));

                        $firstHasOnlyNonType = $firstPartition['type'] === [] && $firstPartition['non_type'] !== [];
                        $secondHasType = $secondPartition['type'] !== [];

                        $firstIsSafe = true;
                        foreach ($firstPartition['non_type'] as $nm) {
                            $name = $this->resolveMethodName($nm);
                            if ($name !== null && ! $this->isSafeNonTypeMatcher($name)) {
                                $firstIsSafe = false;
                                break;
                            }
                        }

                        if ($firstHasOnlyNonType && $secondHasType && $firstIsSafe) {
                            $stmts[$key] = $next;
                            $stmts[$key + 1] = $stmt;
                            $hasChanged = true;
                        }
                    }
                }
            }
        }

        if (! $hasChanged) {
            return null;
        }

        $this->setStatements($node, array_values($stmts));

        return $node;
    }

    /**
     * @param  array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}  $method
     */
    private function resolveMethodName(array $method): ?string
    {
        $nameValue = $method['name'];

        return $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;
    }

    /**
     * @param  array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>  $methods
     * @return array<array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>>
     */
    private function groupIntoUnits(array $methods): array
    {
        $units = [];
        $pending = [];

        foreach ($methods as $m) {
            $pending[] = $m;

            if (! in_array($this->resolveMethodName($m), self::$prefixModifiers, true)) {
                $units[] = $pending;
                $pending = [];
            }
        }

        if ($pending !== []) {
            $units[] = $pending;
        }

        return $units;
    }

    /**
     * @param  array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>  $unit
     */
    private function isTypeUnit(array $unit): bool
    {
        $last = end($unit);

        if ($last === false) {
            return false;
        }

        $name = $this->resolveMethodName($last);

        return $name !== null && $this->isTypeMatcherName($name);
    }

    /**
     * @param  array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>  $methods
     * @return array{type: array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>, non_type: array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>}
     */
    private function partitionTypeAndNonType(array $methods): array
    {
        $type = [];
        $nonType = [];

        foreach ($methods as $m) {
            $name = $this->resolveMethodName($m);

            if ($name !== null && $this->isTypeMatcherName($name)) {
                $type[] = $m;
            } else {
                $nonType[] = $m;
            }
        }

        return ['type' => $type, 'non_type' => $nonType];
    }

    private function isTypeMatcherName(string $name): bool
    {
        return in_array($name, self::$typeMatchers, true);
    }

    /**
     * @see https://pestphp.com/docs/expectations
     * @see https://pestphp.com/docs/higher-order-testing#content-higher-order-expectations
     */
    private function isSafeNonTypeMatcher(string $name): bool
    {
        if (
            str_starts_with($name, 'toBe')
            || str_starts_with($name, 'toContain')
            || str_starts_with($name, 'toEndWith')
            || str_starts_with($name, 'toEqual')
            || str_starts_with($name, 'toHave')
            || str_starts_with($name, 'toMatch')
            || str_starts_with($name, 'toStartWith')
            || str_starts_with($name, 'toThrow')
        ) {
            return true;
        }

        return in_array(
            $name,
            [
                'and', 'dd', 'ddUnless', 'ddWhen', 'each', 'json',
                'match', 'not', 'ray', 'sequence', 'unless', 'when',
            ],
            true
        );
    }

    /**
     * @param  array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>  $methods
     * @return array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>
     */
    private function reorderWithinAndSegments(array $methods): array
    {
        $result = [];
        /** @var array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}> $segment */
        $segment = [];

        $flushSegment = function () use (&$segment, &$result): void {
            $keepAsIs = function () use (&$segment, &$result): void {
                foreach ($segment as $m) {
                    $result[] = $m;
                }

                $segment = [];
            };

            foreach ($segment as $sm) {
                $name = $this->resolveMethodName($sm);

                if ($name === 'each') {
                    $keepAsIs();

                    return;
                }

                if ($name !== null && ! $this->isTypeMatcherName($name) && ! $this->isSafeNonTypeMatcher($name)) {
                    $keepAsIs();

                    return;
                }
            }

            $units = $this->groupIntoUnits($segment);

            $typeUnits = [];
            $nonTypeUnits = [];

            foreach ($units as $unit) {
                if ($this->isTypeUnit($unit)) {
                    $typeUnits[] = $unit;
                } else {
                    $nonTypeUnits[] = $unit;
                }
            }

            if ($typeUnits === [] || $nonTypeUnits === []) {
                $keepAsIs();

                return;
            }

            $needsReorder = false;
            $foundNonType = false;

            foreach ($units as $unit) {
                if ($this->isTypeUnit($unit)) {
                    if ($foundNonType) {
                        $needsReorder = true;

                        break;
                    }
                } else {
                    $foundNonType = true;
                }
            }

            if ($needsReorder) {
                $foundType = false;
                $foundNonAfterType = false;

                foreach ($units as $unit) {
                    if ($this->isTypeUnit($unit)) {
                        if ($foundNonAfterType) {
                            $needsReorder = false;

                            break;
                        }

                        $foundType = true;
                    } elseif ($foundType) {
                        $foundNonAfterType = true;
                    }
                }
            }

            if (! $needsReorder) {
                $keepAsIs();

                return;
            }

            foreach (array_merge($typeUnits, $nonTypeUnits) as $unit) {
                foreach ($unit as $m) {
                    $result[] = $m;
                }
            }

            $segment = [];
        };

        foreach ($methods as $m) {
            if ($this->resolveMethodName($m) === 'and') {
                if ($segment !== []) {
                    $flushSegment();
                }

                $result[] = $m;

                continue;
            }

            $segment[] = $m;
        }

        if ($segment !== []) {
            $flushSegment();
        }

        return $result;
    }
}
