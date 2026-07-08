<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToContainEqualRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToContainEqualRector::class);
};
