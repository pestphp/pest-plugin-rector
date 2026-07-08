<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use RectorPest\AbstractSemanticPestRector;
use RectorPest\Analyzer\PestChainAnalyzer;
use RectorPest\Registry\PestSemanticIssues;
use RectorPest\ValueObject\PestSemanticIssue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Normalizes invalid repeat() counts to Pest's minimum supported value.
 */
final class FixInvalidRepeatValueRector extends AbstractSemanticPestRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Normalizes invalid literal repeat() counts to 1',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
it('retries once', function (): void {
    expect(true)->toBeTrue();
})->repeat(0);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
it('retries once', function (): void {
    expect(true)->toBeTrue();
})->repeat(1);
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    public function getSemanticIssue(): PestSemanticIssue
    {
        return PestSemanticIssues::invalidRepeatValue();
    }

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
        if (! $node->name instanceof Identifier || $node->name->name !== 'repeat') {
            return null;
        }

        if (! PestChainAnalyzer::isPestTestChain($node)) {
            return null;
        }

        $firstArg = $node->args[0] ?? null;
        if (! $firstArg instanceof Arg) {
            return null;
        }

        return $this->normalizeRepeatValue($firstArg) ? $node : null;
    }

    private function normalizeRepeatValue(Arg $arg): bool
    {
        if ($arg->value instanceof Int_) {
            if ($arg->value->value >= 1) {
                return false;
            }

            $arg->value = new Int_(1);

            return true;
        }

        if (! $arg->value instanceof UnaryMinus) {
            return false;
        }

        if (! $arg->value->expr instanceof Int_ || $arg->value->getComments() !== [] || $arg->value->expr->getComments() !== []) {
            return false;
        }

        $arg->value = new Int_(1);

        return true;
    }
}
