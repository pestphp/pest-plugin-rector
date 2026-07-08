<?php

declare(strict_types=1);

namespace Pest\PluginName;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
trait Example // @phpstan-ignore-line
{
    public function example(string $name): TestCase
    {
        expect($name)->toBeString();

        return $this;
    }
}
