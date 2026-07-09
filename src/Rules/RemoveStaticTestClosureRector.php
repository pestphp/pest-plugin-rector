<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractSemanticPestRector;
use Pest\Rector\Registry\PestSemanticIssues;
use Pest\Rector\Support\PestFunctionDetector;
use Pest\Rector\ValueObject\PestSemanticIssue;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveStaticTestClosureRector extends AbstractSemanticPestRector
{
    /** @var list<string> */
    private const STATIC_HOOKS_TO_KEEP = ['beforeAll', 'afterAll'];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes static from Pest test and hook callbacks that use the test case instance',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
it('uses the test case instance', static function (): void {
    expect($this)->not->toBeNull();
});
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
it('uses the test case instance', function (): void {
    expect($this)->not->toBeNull();
});
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    public function getSemanticIssue(): PestSemanticIssue
    {
        return PestSemanticIssues::staticTestClosure();
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
        $functionName = PestFunctionDetector::getFunctionName($node);

        if ($functionName === null || in_array($functionName, self::STATIC_HOOKS_TO_KEEP, true)) {
            return null;
        }

        $closure = PestFunctionDetector::extractClosure($node);
        if ($closure === null || ! $closure->static) {
            return null;
        }

        if (! PestFunctionDetector::closureRequiresInstanceBinding($closure)) {
            return null;
        }

        $closure->static = false;

        return $node;
    }
}
