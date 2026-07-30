<?php

declare(strict_types=1);

use Pest\Rector\Rules\ChainExpectCallsRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ChainExpectCallsRector::class, [
        ChainExpectCallsRector::MERGE_DIFFERENT_VARIABLES => false,
    ]);
};
