<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToBeListRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToBeListRector::class);
};
