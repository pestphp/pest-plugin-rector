<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

use RectorPest\AbstractSemanticPestRector;

/**
 * Represents a possible Rector-based fix for a semantic issue.
 */
final readonly class SemanticFixCandidate
{
    /**
     * @param class-string<AbstractSemanticPestRector> $rectorClass
     */
    public function __construct(
        public PestSemanticIssue $issue,
        public string $rectorClass,
        public string $matchedDiagnosticIdentifier,
    ) {
    }

    /**
     * @param class-string<AbstractSemanticPestRector> $rectorClass
     */
    public static function fromIssue(PestSemanticIssue $issue, string $rectorClass, string $matchedDiagnosticIdentifier): self
    {
        return new self($issue, $rectorClass, $matchedDiagnosticIdentifier);
    }

    public function isAutoFixable(): bool
    {
        return $this->issue->isAutoFixable();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'issueIdentifier' => $this->issue->identifier,
            'matchedDiagnosticIdentifier' => $this->matchedDiagnosticIdentifier,
            'rectorClass' => $this->rectorClass,
            'fixability' => $this->issue->fixability,
            'safetyLevel' => $this->issue->safetyLevel,
            'autofixStrategy' => $this->issue->autofixStrategy,
            'interoperabilityVersion' => $this->issue->interoperabilityVersion,
            'issue' => $this->issue->toArray(),
        ];
    }
}
