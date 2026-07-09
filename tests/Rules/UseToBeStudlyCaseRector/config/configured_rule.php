<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToBeStudlyCaseRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToBeStudlyCaseRector::class);
};
