<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToEqualCanonicalizingRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToEqualCanonicalizingRector::class);
};
