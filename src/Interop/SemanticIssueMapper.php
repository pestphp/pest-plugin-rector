<?php

declare(strict_types=1);

namespace Pest\Rector\Interop;

use Pest\Rector\AbstractSemanticPestRector;
use Pest\Rector\Registry\PestSemanticIssues;
use Pest\Rector\Rules\ConvertBeforeAllInDescribeRector;
use Pest\Rector\Rules\FixInvalidRepeatValueRector;
use Pest\Rector\Rules\RemoveRedundantLiteralTypeExpectationRector;
use Pest\Rector\Rules\RemoveStaticTestClosureRector;
use Pest\Rector\ValueObject\PestSemanticIssue;
use Pest\Rector\ValueObject\SemanticFixCandidate;

final readonly class SemanticIssueMapper
{
    /**
     * @var array<string, list<class-string<AbstractSemanticPestRector>>>
     */
    private const array RULE_MAP = [
        PestSemanticIssues::STATIC_TEST_CLOSURE => [RemoveStaticTestClosureRector::class],
        PestSemanticIssues::BEFORE_ALL_IN_DESCRIBE => [ConvertBeforeAllInDescribeRector::class],
        PestSemanticIssues::AFTER_ALL_IN_DESCRIBE => [ConvertBeforeAllInDescribeRector::class],
        PestSemanticIssues::INVALID_REPEAT_VALUE => [FixInvalidRepeatValueRector::class],
        PestSemanticIssues::REDUNDANT_EXPECTATION => [RemoveRedundantLiteralTypeExpectationRector::class],
    ];

    public function __construct(
        private PestDiagnosticResolver $diagnosticResolver = new PestDiagnosticResolver(),
    ) {}

    public function supportsDiagnostic(string $diagnosticIdentifier): bool
    {
        return $this->diagnosticResolver->supports($diagnosticIdentifier);
    }

    public function supportsIssue(string $issueIdentifier): bool
    {
        return PestSemanticIssues::get($issueIdentifier) instanceof PestSemanticIssue;
    }

    /**
     * @return list<SemanticFixCandidate>
     */
    public function resolveCandidatesForDiagnostic(string $diagnosticIdentifier): array
    {
        $issue = $this->diagnosticResolver->resolve($diagnosticIdentifier);
        if (! $issue instanceof PestSemanticIssue) {
            return [];
        }

        return $this->resolveCandidatesForIssue($issue, $diagnosticIdentifier);
    }

    /**
     * @return list<SemanticFixCandidate>
     */
    public function resolveCandidatesForIssue(PestSemanticIssue|string $issue, ?string $matchedDiagnosticIdentifier = null): array
    {
        $semanticIssue = is_string($issue) ? PestSemanticIssues::get($issue) : $issue;
        if (! $semanticIssue instanceof PestSemanticIssue) {
            return [];
        }

        $rectorClasses = self::RULE_MAP[$semanticIssue->identifier] ?? [];
        $diagnosticIdentifier = $matchedDiagnosticIdentifier ?? $semanticIssue->canonicalDiagnosticIdentifier();

        return array_map(
            static fn (string $rectorClass): SemanticFixCandidate => SemanticFixCandidate::fromIssue(
                $semanticIssue,
                $rectorClass,
                $diagnosticIdentifier,
            ),
            $rectorClasses,
        );
    }
}
