<?php

declare(strict_types=1);

namespace Pest\Rector\ValueObject\CodeSample;

use InvalidArgumentException;

abstract class AbstractCodeSample
{
    /**
     * @var non-empty-string
     */
    private readonly string $goodCode;

    /**
     * @var non-empty-string
     */
    private readonly string $badCode;

    public function __construct(string $badCode, string $goodCode)
    {
        $badCode = mb_trim($badCode);
        $goodCode = mb_trim($goodCode);

        if ($badCode === '') {
            throw new InvalidArgumentException('Bad sample code cannot be empty.');
        }

        if ($goodCode === '') {
            throw new InvalidArgumentException('Good sample code cannot be empty.');
        }

        if ($goodCode === $badCode) {
            throw new InvalidArgumentException(sprintf('Good and bad code cannot be identical: "%s".', $goodCode));
        }

        $this->goodCode = $goodCode;
        $this->badCode = $badCode;
    }

    final public function getGoodCode(): string
    {
        return $this->goodCode;
    }

    final public function getBadCode(): string
    {
        return $this->badCode;
    }
}
