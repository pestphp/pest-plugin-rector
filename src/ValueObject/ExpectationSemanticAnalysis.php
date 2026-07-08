<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

final readonly class ExpectationSemanticAnalysis
{
    public function __construct(
        public string $matcher,
        public string $expectedCategory,
        public string $literalCategory,
        public bool $negated,
        public bool $matches,
    ) {
    }

    public static function forDeterministicLiteralTypeCheck(
        string $matcher,
        string $expectedCategory,
        string $literalCategory,
        bool $negated,
        bool $matches,
    ): self {
        return new self($matcher, $expectedCategory, $literalCategory, $negated, $matches);
    }

    public function isRedundant(): bool
    {
        return $this->matches !== $this->negated;
    }

    public function isImpossible(): bool
    {
        return $this->matches === $this->negated;
    }
}
