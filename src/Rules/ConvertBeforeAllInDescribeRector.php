<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractSemanticPestRector;
use Pest\Rector\Analyzer\HookSemanticAnalyzer;
use Pest\Rector\Registry\PestSemanticIssues;
use Pest\Rector\Support\PestFunctionDetector;
use Pest\Rector\ValueObject\PestSemanticIssue;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConvertBeforeAllInDescribeRector extends AbstractSemanticPestRector
{
    /**
     * @var array<string, string>
     */
    private const array HOOK_REPLACEMENTS = [
        'beforeAll' => 'beforeEach',
        'afterAll' => 'afterEach',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces invalid beforeAll() and afterAll() hooks inside describe() with beforeEach() and afterEach()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
describe('users', function (): void {
    beforeAll(function (): void {
        refreshDatabase();
    });
});
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
describe('users', function (): void {
    beforeEach(function (): void {
        refreshDatabase();
    });
});
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    public function getSemanticIssue(): PestSemanticIssue
    {
        return PestSemanticIssues::beforeAllInDescribe();
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param  FuncCall  $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! PestFunctionDetector::isDescribeFunction($node)) {
            return null;
        }

        $closure = PestFunctionDetector::extractClosure($node);

        if (! $closure instanceof Closure) {
            return null;
        }

        $invalidHooks = HookSemanticAnalyzer::findInvalidDescribeHooks($closure, self::HOOK_REPLACEMENTS);

        if ($invalidHooks === []) {
            return null;
        }

        foreach ($invalidHooks as $invalidHook) {
            $name = PestFunctionDetector::getFunctionName($invalidHook);

            if ($name === null) {
                continue;
            }

            $invalidHook->name = new Name(self::HOOK_REPLACEMENTS[$name]);
        }

        return $node;
    }
}
