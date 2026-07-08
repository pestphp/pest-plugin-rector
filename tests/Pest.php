<?php

declare(strict_types=1);

use Tests\TestCase;

pest()->extend(TestCase::class)->in('Rules');

expect()->extend('toBeOne', fn () => $this->toBe(1));

function something(): void
{
    //
}
