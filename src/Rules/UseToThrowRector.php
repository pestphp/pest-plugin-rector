<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeVisitor;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts try/catch patterns in tests to Pest's toThrow() matcher
 */
final class UseToThrowRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts try/catch patterns in Pest tests to expect()->toThrow()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
test('it throws an error', function () {
    try {
        doSomething();
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('error');
    }
});
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
test('it throws an error', function () {
    expect(fn() => doSomething())->toThrow(RuntimeException::class, 'error');
});
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNames($node, ['test', 'it', 'describe', 'beforeEach', 'afterEach', 'beforeAll', 'afterAll'])) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->args as $arg) {
            if (! $arg instanceof Arg) {
                continue;
            }

            if (! $arg->value instanceof Closure) {
                continue;
            }

            if ($this->transformTestClosure($arg->value)) {
                $hasChanged = true;
            }
        }

        return $hasChanged ? $node : null;
    }

    private function transformTestClosure(Closure $closure): bool
    {
        $hasChanged = false;

        $this->traverseNodesWithCallable([$closure], function (Node $node) use (&$hasChanged, $closure): int|Node|null {
            if ($node instanceof Closure && $node !== $closure) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }

            if (! $node instanceof TryCatch) {
                return null;
            }

            $result = $this->convertTryCatch($node);
            if (! $result instanceof MethodCall) {
                return null;
            }

            $hasChanged = true;

            return new Expression($result);
        });

        return $hasChanged;
    }

    private function convertTryCatch(TryCatch $tryCatch): ?MethodCall
    {
        if (count($tryCatch->catches) !== 1) {
            return null;
        }

        if ($tryCatch->finally instanceof Finally_) {
            return null;
        }

        $catch = $tryCatch->catches[0];

        if (count($catch->types) !== 1) {
            return null;
        }

        $tryStmts = $tryCatch->stmts;
        if ($tryStmts === []) {
            return null;
        }

        $exceptionClass = $catch->types[0];
        $message = $this->extractMessageAssertion($catch);

        if ($catch->stmts !== [] && !$message instanceof Expr) {
            return null;
        }

        $arrowFunction = new ArrowFunction([
            'expr' => $this->buildTryExpression($tryStmts),
        ]);

        $expectCall = new FuncCall(new Name('expect'), [new Arg($arrowFunction)]);

        $toThrowArgs = [new Arg(new ClassConstFetch($exceptionClass, 'class'))];
        if ($message instanceof Expr) {
            $toThrowArgs[] = new Arg($message);
        }

        return new MethodCall($expectCall, 'toThrow', $toThrowArgs);
    }

    /**
     * Build the expression for the try block body
     */
    /**
     * @param array<Node\Stmt> $stmts
     */
    private function buildTryExpression(array $stmts): Expr
    {
        if (count($stmts) === 1 && $stmts[0] instanceof Expression) {
            return $stmts[0]->expr;
        }

        return new FuncCall(
            new ArrowFunction([
                'expr' => count($stmts) === 1 && $stmts[0] instanceof Expression
                    ? $stmts[0]->expr
                    : new Int_(0),
            ])
        );
    }

    /**
     * Extract message from expect($e->getMessage())->toBe('...')
     */
    private function extractMessageAssertion(Catch_ $catch): ?Expr
    {
        if ($catch->stmts === []) {
            return null;
        }

        if (count($catch->stmts) !== 1) {
            return null;
        }

        $stmt = $catch->stmts[0];
        if (! $stmt instanceof Expression) {
            return null;
        }

        if (! $stmt->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $stmt->expr;

        if (! $this->isExpectChain($methodCall)) {
            return null;
        }

        if (! $this->isName($methodCall->name, 'toBe')) {
            return null;
        }

        if (count($methodCall->args) !== 1) {
            return null;
        }

        $expectArg = $this->getExpectArgument($methodCall);
        if (! $expectArg instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($expectArg->name, 'getMessage')) {
            return null;
        }

        if (! $expectArg->var instanceof Variable) {
            return null;
        }

        if (!$catch->var instanceof Variable) {
            return null;
        }

        $catchVarName = $this->getName($catch->var);
        if ($catchVarName === null) {
            return null;
        }

        if (! $this->isName($expectArg->var, $catchVarName)) {
            return null;
        }

        $arg = $methodCall->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        return $arg->value;
    }
}
