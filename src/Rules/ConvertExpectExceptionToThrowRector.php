<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts PHPUnit expectException() patterns to Pest's toThrow() matcher.
 */
final class ConvertExpectExceptionToThrowRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const SUPPORTED_METHODS = [
        'expectException',
        'expectExceptionMessage',
        'expectExceptionObject',
    ];

    /**
     * @var string[]
     */
    private const ALL_EXPECT_EXCEPTION_METHODS = [
        'expectException',
        'expectExceptionMessage',
        'expectExceptionObject',
        'expectExceptionCode',
        'expectExceptionMessageMatches',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expectException() and expectExceptionMessage() patterns to expect()->toThrow()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$this->expectException(RuntimeException::class);
$this->expectExceptionMessage('error');
doSomething();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect(fn () => doSomething())->toThrow(RuntimeException::class, 'error');
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

        $newStmts = [];
        $i = 0;

        while ($i < count($stmts)) {
            $conversion = $this->collectConversion($stmts, $i);

            if ($conversion === null) {
                $newStmts[] = $stmts[$i];
                $i++;

                continue;
            }

            $newStmts[] = new Expression(
                $this->buildToThrowCall($conversion['exception'], $conversion['message'], $conversion['action'])
            );
            $i += $conversion['consumed'];
            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        $this->setStatements($node, $newStmts);

        return $node;
    }

    /**
     * @param array<Node\Stmt> $stmts
      * Supports both `expectException()`-then-message and message-first ordering when the
      * pattern stays local to consecutive expectation setup calls followed by one action.
     * @return array{action: Expr, consumed: int, exception: Expr, message: Expr|null}|null
     */
    private function collectConversion(array $stmts, int $startPos): ?array
    {
        $exception = null;
        $exceptionMethod = null;
        $message = null;
        $consumedExpectations = 0;
        $count = count($stmts);

        for ($i = $startPos; $i < $count; $i++) {
            $stmt = $stmts[$i];

            if (! $stmt instanceof Expression || ! $stmt->expr instanceof MethodCall) {
                break;
            }

            $methodCall = $stmt->expr;
            $methodName = $this->getExpectationMethodName($methodCall);

            if ($methodName === null || ! in_array($methodName, self::SUPPORTED_METHODS, true)) {
                break;
            }

            if (count($methodCall->args) !== 1) {
                return null;
            }

            $arg = $methodCall->args[0];
            if (! $arg instanceof Arg) {
                return null;
            }

            if ($methodName === 'expectExceptionMessage') {
                if ($exceptionMethod === 'expectExceptionObject') {
                    return null;
                }

                if ($message instanceof Expr) {
                    return null;
                }

                $message = $arg->value;
                $consumedExpectations++;

                continue;
            }

            if ($exception instanceof Expr || ($methodName === 'expectExceptionObject' && $message instanceof Expr)) {
                return null;
            }

            $exception = $arg->value;
            $exceptionMethod = $methodName;
            $consumedExpectations++;
        }

        if (! $exception instanceof Expr || $consumedExpectations === 0) {
            return null;
        }

        $actionPos = $startPos + $consumedExpectations;
        if (! isset($stmts[$actionPos]) || ! $stmts[$actionPos] instanceof Expression) {
            return null;
        }

        $action = $stmts[$actionPos]->expr;
        if ($action instanceof MethodCall && $this->getExpectationMethodName($action, true) !== null) {
            return null;
        }

        return [
            'action' => $action,
            'consumed' => $consumedExpectations + 1,
            'exception' => $exception,
            'message' => $message,
        ];
    }

    private function buildToThrowCall(Expr $exception, ?Expr $message, Expr $action): MethodCall
    {
        $expectCall = new FuncCall(
            new Name('expect'),
            [new Arg(new ArrowFunction(['expr' => $action]))]
        );

        $args = [new Arg($exception)];

        if ($message instanceof Expr) {
            $args[] = new Arg($message);
        }

        return new MethodCall($expectCall, 'toThrow', $args);
    }

    private function getExpectationMethodName(MethodCall $methodCall, bool $includeUnsupported = false): ?string
    {
        if (! $this->isThisCall($methodCall)) {
            return null;
        }

        $methodName = $this->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }

        $supportedMethods = $includeUnsupported ? self::ALL_EXPECT_EXCEPTION_METHODS : self::SUPPORTED_METHODS;

        return in_array($methodName, $supportedMethods, true) ? $methodName : null;
    }

    private function isThisCall(MethodCall $methodCall): bool
    {
        return $methodCall->var instanceof Variable && $this->isName($methodCall->var, 'this');
    }
}
