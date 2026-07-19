<?php

declare(strict_types=1);

use Pest\Rector\Rules\ChainExpectCallsRector;
use Pest\Rector\Rules\UseToBeEmptyRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ChainExpectCallsRector::class);
    $rectorConfig->rule(UseToBeEmptyRector::class);
};
