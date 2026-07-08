<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\ChainExpectCallsRector;
use RectorPest\Rules\UseEachModifierRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ChainExpectCallsRector::class);
    $rectorConfig->rule(UseEachModifierRector::class);
};
