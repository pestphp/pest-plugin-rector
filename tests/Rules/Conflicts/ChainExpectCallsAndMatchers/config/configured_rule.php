<?php

declare(strict_types=1);

use Pest\Rector\Rules\ChainExpectCallsRector;
use Pest\Rector\Rules\UseToHaveCountRector;
use Pest\Rector\Rules\UseToHaveLengthRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToHaveLengthRector::class);
    $rectorConfig->rule(UseToHaveCountRector::class);
    $rectorConfig->rule(ChainExpectCallsRector::class);
};
