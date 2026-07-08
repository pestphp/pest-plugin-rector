<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\ConvertBeforeAllInDescribeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ConvertBeforeAllInDescribeRector::class);
};
