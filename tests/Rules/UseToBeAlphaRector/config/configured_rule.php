<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToBeAlphaRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToBeAlphaRector::class);
};
