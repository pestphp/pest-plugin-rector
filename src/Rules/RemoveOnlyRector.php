<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use Pest\Rector\Analyzer\PestChainAnalyzer;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveOnlyRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const array PEST_TEST_FUNCTIONS = [
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
     * @param  MethodCall  $node
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

        if (! $this->isPestTestChain($node)) {
            return null;
        }

        return $node->var;
    }

    private function isPestTestChain(MethodCall $methodCall): bool
    {
        $root = PestChainAnalyzer::getRootFuncCall($methodCall);

        return $root instanceof FuncCall && $this->isNames($root, self::PEST_TEST_FUNCTIONS);
    }
}
