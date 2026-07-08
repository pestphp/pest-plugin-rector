<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

/**
 * Metadata describing a semantic issue that can be auto-fixed by a Rector rule.
 */
final readonly class PestSemanticIssue
{
    public const INTEROPERABILITY_VERSION = '1.0.0';

    /**
     * @param non-empty-list<string> $diagnosticIdentifiers
     * @param list<string> $tags
     */
    public function __construct(
        public string $identifier,
        public string $defaultMessage,
        public array $diagnosticIdentifiers,
        public string $category,
        public string $fixCategory,
        public string $fixability,
        public string $severity,
        public string $safetyLevel,
        public string $confidence,
        public array $tags = [],
        public ?string $matcherCategory = null,
        public string $autofixStrategy = PestSemanticAutofixStrategy::NONE,
        public string $interoperabilityVersion = self::INTEROPERABILITY_VERSION,
        public ?string $issueFamily = null,
        public ?string $semanticGroup = null,
    ) {
    }

    public function canonicalDiagnosticIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return non-empty-list<string>
     */
    public function allDiagnosticIdentifiers(): array
    {
        $diagnosticIdentifiers = array_values(array_filter(
            array_unique($this->diagnosticIdentifiers),
            fn (string $diagnosticIdentifier): bool => $diagnosticIdentifier !== $this->identifier,
        ));

        return [$this->identifier, ...$diagnosticIdentifiers];
    }

    public function matchesDiagnosticIdentifier(string $diagnosticIdentifier): bool
    {
        return in_array($diagnosticIdentifier, $this->allDiagnosticIdentifiers(), true);
    }

    public function isAutoFixable(): bool
    {
        return $this->fixability === PestSemanticFixability::AUTO_FIXABLE;
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'defaultMessage' => $this->defaultMessage,
            'diagnosticIdentifiers' => $this->allDiagnosticIdentifiers(),
            'category' => $this->category,
            'fixCategory' => $this->fixCategory,
            'fixability' => $this->fixability,
            'severity' => $this->severity,
            'safetyLevel' => $this->safetyLevel,
            'confidence' => $this->confidence,
            'tags' => $this->tags,
            'matcherCategory' => $this->matcherCategory,
            'autofixStrategy' => $this->autofixStrategy,
            'interoperabilityVersion' => $this->interoperabilityVersion,
            'issueFamily' => $this->issueFamily,
            'semanticGroup' => $this->semanticGroup,
        ];
    }
}
