<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Pest2ToPest3\UsesToExtendRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UsesToExtendRector::class);
};
