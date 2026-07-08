<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Removes only() from all tests.
 *
 * Before: test()->only()
 * After:  test()
 */
final class RemoveOnlyRector extends AbstractRector
{
    /**
     * Pest test function names that can have ->only() called on them.
     *
     * @var string[]
     */
    private const PEST_TEST_FUNCTIONS = [
        'test',
        'it',
        'describe',
        'todo',
        'beforeEach',
        'afterEach',
        'beforeAll',
        'afterAll',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes only() from all tests',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
test()->only();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
test();
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->name instanceof Identifier) {
            return null;
        }

        $methodName = $node->name->name;

        if ($methodName !== 'only') {
            return null;
        }

        // Only remove ->only() if called on a Pest test function
        if (! $this->isPestTestChain($node)) {
            return null;
        }

        return $node->var;
    }

    /**
     * Check if the method chain originates from a Pest test function.
     */
    private function isPestTestChain(MethodCall $methodCall): bool
    {
        $current = $methodCall->var;

        // Walk up the method chain to find the root
        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        // The root should be a FuncCall (e.g., test(), it(), describe())
        if (! $current instanceof FuncCall) {
            return false;
        }

        return $this->isNames($current, self::PEST_TEST_FUNCTIONS);
    }
}
