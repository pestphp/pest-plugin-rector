<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToBeNanRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToBeNanRector::class);
};
