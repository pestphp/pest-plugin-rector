<?php

declare(strict_types=1);

namespace Pest\Rector\ValueObject;

use InvalidArgumentException;
use Pest\Rector\ValueObject\CodeSample\CodeSample;
use Pest\Rector\ValueObject\CodeSample\ConfiguredCodeSample;

final readonly class RuleDefinition
{
    /**
     * @param  list<CodeSample|ConfiguredCodeSample>  $codeSamples
     */
    public function __construct(
        private string $description,
        private array $codeSamples,
    ) {
        if ($codeSamples === []) {
            throw new InvalidArgumentException('Provide at least one code sample, so people can practically see what the rule does.');
        }
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return list<CodeSample|ConfiguredCodeSample>
     */
    public function getCodeSamples(): array
    {
        return $this->codeSamples;
    }
}
