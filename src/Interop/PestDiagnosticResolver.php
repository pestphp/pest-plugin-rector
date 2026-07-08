<?php

declare(strict_types=1);

namespace RectorPest\Interop;

use RectorPest\Registry\PestSemanticIssues;
use RectorPest\ValueObject\PestSemanticIssue;

/**
 * Resolves machine-readable diagnostic identifiers to canonical semantic issues.
 */
final class PestDiagnosticResolver
{
    /** @var array<string, PestSemanticIssue>|null */
    private ?array $identifierMap = null;

    /** @var array<string, string>|null */
    private ?array $canonicalIdentifierMap = null;

    /** @var list<string>|null */
    private ?array $supportedDiagnosticIdentifiers = null;

    /**
     * @return array<string, PestSemanticIssue>
     */
    private function identifierMap(): array
    {
        if (is_array($this->identifierMap)) {
            return $this->identifierMap;
        }

        $identifierMap = [];
        $canonicalIdentifierMap = [];

        foreach (PestSemanticIssues::all() as $issue) {
            foreach ($issue->allDiagnosticIdentifiers() as $identifier) {
                $identifierMap[$identifier] = $issue;
                $canonicalIdentifierMap[$identifier] = $issue->identifier;
            }
        }

        $this->identifierMap = $identifierMap;
        $this->canonicalIdentifierMap = $canonicalIdentifierMap;
        $this->supportedDiagnosticIdentifiers = array_keys($identifierMap);

        return $this->identifierMap;
    }

    public function resolve(string $diagnosticIdentifier): ?PestSemanticIssue
    {
        return $this->identifierMap()[$diagnosticIdentifier] ?? null;
    }

    public function canonicalize(string $diagnosticIdentifier): ?string
    {
        $this->identifierMap();

        return $this->canonicalIdentifierMap[$diagnosticIdentifier] ?? null;
    }

    public function supports(string $diagnosticIdentifier): bool
    {
        return $this->resolve($diagnosticIdentifier) instanceof PestSemanticIssue;
    }

    /**
     * @param list<string> $diagnosticIdentifiers
     * @return list<PestSemanticIssue>
     */
    public function resolveAll(array $diagnosticIdentifiers): array
    {
        $resolvedIssues = [];

        foreach ($diagnosticIdentifiers as $diagnosticIdentifier) {
            $issue = $this->resolve($diagnosticIdentifier);

            if (! $issue instanceof PestSemanticIssue) {
                continue;
            }

            $resolvedIssues[$issue->identifier] = $issue;
        }

        return array_values($resolvedIssues);
    }

    /**
     * @return list<string>
     */
    public function supportedDiagnosticIdentifiers(): array
    {
        $this->identifierMap();

        return $this->supportedDiagnosticIdentifiers ?? [];
    }
}
