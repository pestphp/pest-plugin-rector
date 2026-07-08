<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\PhpParser\Node\FileNode;
use Rector\PhpParser\Parser\SimplePhpParser;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Throwable;

/**
 * Removes local Pest uses that are provably supplied by tests/Pest.php.
 */
final class RemoveRedundantPestUsesRector extends AbstractRector
{
    /** @var array<string, list<array{classNames: list<string>, paths: list<string>}>> */
    private array $globalUsesByPestFile = [];

    /** @var array<string, true> */
    private const GLOBAL_CHAIN_TOLERATED_METHODS = [
        'extend' => true,
        'use' => true,
        'uses' => true,
    ];

    public function __construct(
        private readonly SimplePhpParser $simplePhpParser
    ) {
    }

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes redundant local Pest uses already configured globally in tests/Pest.php',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
// tests/Pest.php contains:
// pest()->use(RefreshDatabase::class)->in('Feature');

// tests/Feature/UserTest.php
pest()->use(RefreshDatabase::class, SomeOtherTrait::class);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
// tests/Feature/UserTest.php
pest()->use(SomeOtherTrait::class);
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
        return [FileNode::class];
    }

    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?FileNode
    {
        $globallyAppliedClasses = $this->resolveGloballyAppliedClasses($this->getFile()->getFilePath());
        if ($globallyAppliedClasses === []) {
            return null;
        }

        [$statements, $hasChanged] = $this->refactorStatements(
            $node->stmts,
            array_fill_keys($globallyAppliedClasses, true)
        );
        if (! $hasChanged) {
            return null;
        }

        $node->stmts = $statements;

        return $node;
    }

    /**
     * @param array<Stmt> $statements
     * @param array<string, true> $globallyAppliedClassMap
     * @return array{0: array<Stmt>, 1: bool}
     */
    private function refactorStatements(array $statements, array $globallyAppliedClassMap): array
    {
        $hasChanged = false;
        $updatedStatements = [];

        foreach ($statements as $statement) {
            if ($statement instanceof Namespace_) {
                [$statement->stmts, $hasNamespaceChanged] = $this->refactorStatements($statement->stmts, $globallyAppliedClassMap);
                $hasChanged = $hasChanged || $hasNamespaceChanged;
                $updatedStatements[] = $statement;

                continue;
            }

            if (! $statement instanceof Expression) {
                $updatedStatements[] = $statement;

                continue;
            }

            $updatedStatement = $this->refactorLocalUseStatement($statement, $globallyAppliedClassMap);
            if (! $updatedStatement instanceof Expression) {
                $hasChanged = true;

                continue;
            }

            if ($updatedStatement !== $statement) {
                $hasChanged = true;
            }

            $updatedStatements[] = $updatedStatement;
        }

        return [$updatedStatements, $hasChanged];
    }

    /**
     * @param array<string, true> $globallyAppliedClassMap
     */
    private function refactorLocalUseStatement(Expression $statement, array $globallyAppliedClassMap): ?Expression
    {
        $localUse = $this->matchLocalUse($statement->expr);
        if (! $localUse instanceof FuncCall && ! $localUse instanceof MethodCall) {
            return $statement;
        }

        $remainingArgs = [];
        $hasRemovedArgument = false;

        foreach ($localUse->args as $arg) {
            if (! $arg instanceof Arg) {
                $remainingArgs[] = $arg;

                continue;
            }

            $className = $this->resolveLocalClassName($arg->value);
            if ($className === null || ! isset($globallyAppliedClassMap[$className])) {
                $remainingArgs[] = $arg;

                continue;
            }

            $hasRemovedArgument = true;
        }

        if (! $hasRemovedArgument) {
            return $statement;
        }

        if ($remainingArgs === []) {
            return null;
        }

        $localUse->args = $remainingArgs;

        return $statement;
    }

    private function matchLocalUse(Expr $expr): FuncCall|MethodCall|null
    {
        if ($expr instanceof FuncCall && $this->isName($expr, 'uses')) {
            return $expr;
        }

        if (! $expr instanceof MethodCall || ! $expr->name instanceof Identifier) {
            return null;
        }

        $methodName = strtolower($expr->name->toString());
        if (! in_array($methodName, ['use', 'uses'], true)) {
            return null;
        }

        if (! $expr->var instanceof FuncCall || ! $this->isName($expr->var, 'pest')) {
            return null;
        }

        return $expr;
    }

    private function resolveLocalClassName(Expr $expr): ?string
    {
        if (! $expr instanceof ClassConstFetch || ! $expr->class instanceof Name) {
            return null;
        }

        if (! $expr->name instanceof Identifier || strtolower($expr->name->toString()) !== 'class') {
            return null;
        }

        return $this->resolveClassName($expr->class);
    }

    /**
     * @return list<string>
     */
    private function resolveGloballyAppliedClasses(string $currentFilePath): array
    {
        $testDirectory = $this->findTestDirectory($currentFilePath);
        if ($testDirectory === null) {
            return [];
        }

        $pestFile = $testDirectory . DIRECTORY_SEPARATOR . 'Pest.php';
        if (! is_file($pestFile) || $this->pathsAreEqual($currentFilePath, $pestFile)) {
            return [];
        }

        $relativeFilePath = $this->relativePath($testDirectory, $currentFilePath);
        if ($relativeFilePath === null) {
            return [];
        }

        $globalUses = $this->globalUsesByPestFile[$pestFile] ??= $this->parseGlobalUses($pestFile);
        $classNameMap = [];

        foreach ($globalUses as $globalUse) {
            foreach ($globalUse['paths'] as $path) {
                if (! $this->pathCoversFile($path, $relativeFilePath)) {
                    continue;
                }

                foreach ($globalUse['classNames'] as $className) {
                    $classNameMap[$className] = true;
                }

                break;
            }
        }

        return array_keys($classNameMap);
    }

    private function findTestDirectory(string $currentFilePath): ?string
    {
        $directory = dirname($currentFilePath);

        while (true) {
            if (strtolower(basename($directory)) === 'tests') {
                return $directory;
            }

            $parent = dirname($directory);
            if ($parent === $directory) {
                return null;
            }

            $directory = $parent;
        }
    }

    /**
     * @return list<array{classNames: list<string>, paths: list<string>}>
     */
    private function parseGlobalUses(string $pestFile): array
    {
        try {
            $nodes = $this->simplePhpParser->parseFile($pestFile);
            $nodes = (new NodeTraverser(new NameResolver()))->traverse($nodes);
        } catch (Throwable) {
            return [];
        }

        $globalUses = [];

        foreach ($nodes as $node) {
            if (! $node instanceof Expression) {
                continue;
            }

            if (! $node->expr instanceof MethodCall) {
                continue;
            }

            $globalUse = $this->matchGlobalUse($node->expr);
            if ($globalUse !== null) {
                $globalUses[] = $globalUse;
            }
        }

        return $globalUses;
    }

    /**
     * @return array{classNames: list<string>, paths: list<string>}|null
     */
    private function matchGlobalUse(MethodCall $outerCall): ?array
    {
        if (! $this->isStaticName($outerCall->name, 'in')) {
            return null;
        }

        $paths = $this->resolveStaticPaths($outerCall->args);
        if ($paths === []) {
            return null;
        }

        if ($outerCall->var instanceof FuncCall && $this->isName($outerCall->var, 'uses')) {
            $classNames = $this->resolveStaticClassNames($outerCall->var->args);
            if ($classNames === []) {
                return null;
            }

            return [
                'classNames' => $classNames,
                'paths' => $paths,
            ];
        }

        $classNameMap = [];
        $current = $outerCall->var;

        while ($current instanceof MethodCall) {
            if (! $current->name instanceof Identifier) {
                return null;
            }

            $methodName = strtolower($current->name->toString());
            if (! isset(self::GLOBAL_CHAIN_TOLERATED_METHODS[$methodName])) {
                $current = $current->var;

                continue;
            }

            if (in_array($methodName, ['use', 'uses'], true)) {
                $configuredClassNames = $this->resolveStaticClassNames($current->args);
                if ($configuredClassNames === []) {
                    return null;
                }

                foreach ($configuredClassNames as $configuredClassName) {
                    $classNameMap[$configuredClassName] = true;
                }
            }

            $current = $current->var;
        }

        if (! $current instanceof FuncCall || ! $current->name instanceof Name || strtolower($current->name->toString()) !== 'pest') {
            return null;
        }

        if ($classNameMap === []) {
            return null;
        }

        return [
            'classNames' => array_keys($classNameMap),
            'paths' => $paths,
        ];
    }

    /**
     * @param array<Arg|Node\VariadicPlaceholder> $args
     * @return list<string>
     */
    private function resolveStaticClassNames(array $args): array
    {
        if ($args === []) {
            return [];
        }

        $classNames = [];

        foreach ($args as $arg) {
            if (! $arg instanceof Arg || ! $arg->value instanceof ClassConstFetch) {
                return [];
            }

            $classConstFetch = $arg->value;
            if (! $classConstFetch->class instanceof Name || ! $classConstFetch->name instanceof Identifier) {
                return [];
            }

            if (strtolower($classConstFetch->name->toString()) !== 'class') {
                return [];
            }

            $classNames[] = $this->resolveClassName($classConstFetch->class);
        }

        return $classNames;
    }

    private function resolveClassName(Name $name): string
    {
        $resolvedName = $name->getAttribute('resolvedName');
        if ($resolvedName instanceof Name) {
            return ltrim($resolvedName->toString(), '\\');
        }

        return $this->getName($name);
    }

    /**
     * @param array<Arg|Node\VariadicPlaceholder> $args
     * @return list<string>
     */
    private function resolveStaticPaths(array $args): array
    {
        if ($args === []) {
            return [];
        }

        $paths = [];

        foreach ($args as $arg) {
            if (! $arg instanceof Arg || ! $arg->value instanceof String_) {
                return [];
            }

            $path = str_replace('\\', '/', $arg->value->value);
            if (str_starts_with($path, '/')) {
                return [];
            }

            $path = trim($path, '/');

            if ($path === '' || preg_match('#(^|/)\.\.(/|$)|[*?\[\]{}]#', $path) === 1 || str_contains($path, ':')) {
                return [];
            }

            $paths[] = $path;
        }

        return $paths;
    }

    private function isStaticName(Identifier|Expr $name, string $expected): bool
    {
        return $name instanceof Identifier && strtolower($name->toString()) === $expected;
    }

    private function relativePath(string $directory, string $filePath): ?string
    {
        $normalizedDirectory = rtrim($this->normalizePath($directory), '/');
        $normalizedFilePath = $this->normalizePath($filePath);
        $prefix = $normalizedDirectory . '/';

        if (! $this->startsWithPath($normalizedFilePath, $prefix)) {
            return null;
        }

        return substr($normalizedFilePath, strlen($prefix));
    }

    private function pathCoversFile(string $configuredPath, string $relativeFilePath): bool
    {
        if ($this->pathsAreEqual($configuredPath, $relativeFilePath)) {
            return true;
        }

        return $this->startsWithPath($relativeFilePath, rtrim($configuredPath, '/') . '/');
    }

    private function pathsAreEqual(string $first, string $second): bool
    {
        $first = $this->normalizePath($first);
        $second = $this->normalizePath($second);

        return DIRECTORY_SEPARATOR === '\\' ? strcasecmp($first, $second) === 0 : $first === $second;
    }

    private function startsWithPath(string $path, string $prefix): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return str_starts_with(strtolower($path), strtolower($prefix));
        }

        return str_starts_with($path, $prefix);
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
