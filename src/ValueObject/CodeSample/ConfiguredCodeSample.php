<?php

declare(strict_types=1);

namespace Pest\Rector\ValueObject\CodeSample;

use InvalidArgumentException;

final class ConfiguredCodeSample extends AbstractCodeSample
{
    /**
     * @param  array<string, mixed>  $configuration
     */
    public function __construct(string $badCode, string $goodCode, private readonly array $configuration)
    {
        if ($configuration === []) {
            throw new InvalidArgumentException(sprintf('Configuration cannot be empty. Look for "%s".', $badCode));
        }

        parent::__construct($badCode, $goodCode);
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
