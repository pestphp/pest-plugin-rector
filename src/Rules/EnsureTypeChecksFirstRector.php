<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\VariadicPlaceholder;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
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
                            $nameValue = $nm['name'];
                            $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;
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
     * @param  array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>  $methods
     * @return array{type: array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>, non_type: array<array{name: Expr|Identifier|string, args: array<Arg|VariadicPlaceholder>}>}
     */
    private function partitionTypeAndNonType(array $methods): array
    {
        $type = [];
        $nonType = [];

        foreach ($methods as $m) {
            $nameValue = $m['name'];
            $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;

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
            foreach ($segment as $sm) {
                $nameValue = $sm['name'];
                $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;
                if ($name === 'each') {
                    foreach ($segment as $m) {
                        $result[] = $m;
                    }

                    $segment = [];

                    return;
                }
            }

            $partitioned = $this->partitionTypeAndNonType($segment);

            foreach ($partitioned['non_type'] as $nm) {
                $nameValue = $nm['name'];
                $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;
                if ($name !== null && ! $this->isSafeNonTypeMatcher($name)) {
                    foreach ($segment as $m) {
                        $result[] = $m;
                    }

                    $segment = [];

                    return;
                }
            }

            $needsReorder = false;
            $hasType = $partitioned['type'] !== [];
            $hasNonType = $partitioned['non_type'] !== [];

            if ($hasType && $hasNonType) {
                $foundNonType = false;
                foreach ($segment as $m) {
                    $nameValue = $m['name'];
                    $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;

                    if ($name !== null && $this->isTypeMatcherName($name)) {
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
                    $foundTypeAfterNonAfterType = false;
                    foreach ($segment as $m) {
                        $nameValue = $m['name'];
                        $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;

                        if ($name !== null && $this->isTypeMatcherName($name)) {
                            if ($foundNonAfterType) {
                                $foundTypeAfterNonAfterType = true;
                                break;
                            }

                            $foundType = true;
                        } elseif ($foundType) {
                            $foundNonAfterType = true;
                        }
                    }

                    if ($foundTypeAfterNonAfterType) {
                        $needsReorder = false;
                    }
                }
            }

            if ($needsReorder) {
                $prefixBeforeType = [];
                $otherNonType = [];

                $foundType = false;
                foreach ($segment as $m) {
                    $nameValue = $m['name'];
                    $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;

                    if ($name !== null && $this->isTypeMatcherName($name)) {
                        $foundType = true;
                        break;
                    }

                    if (in_array($name, self::$prefixModifiers, true)) {
                        $prefixBeforeType[] = $m;
                    }
                }

                $remainingPrefixes = $prefixBeforeType;
                foreach ($partitioned['non_type'] as $m) {
                    $nameValue = $m['name'];
                    $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;
                    $isPrefix = false;
                    foreach ($remainingPrefixes as $idx => $p) {
                        $pNameValue = $p['name'];
                        $pName = $pNameValue instanceof Node ? $this->getName($pNameValue) : $pNameValue;
                        if ($pName === $name) {
                            $isPrefix = true;
                            unset($remainingPrefixes[$idx]);
                            break;
                        }
                    }

                    if (! $isPrefix) {
                        $otherNonType[] = $m;
                    }
                }

                foreach (array_merge($prefixBeforeType, $partitioned['type'], $otherNonType) as $m) {
                    $result[] = $m;
                }
            } else {
                foreach ($segment as $m) {
                    $result[] = $m;
                }
            }

            $segment = [];
        };

        foreach ($methods as $m) {
            $nameValue = $m['name'];
            $name = $nameValue instanceof Node ? $this->getName($nameValue) : $nameValue;

            if ($name === 'and') {
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
