<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseInstanceOfMatcherRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseInstanceOfMatcherRector::class);
};
