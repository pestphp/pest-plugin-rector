<?php

declare(strict_types=1);

use Pest\Rector\Rules\Pest2ToPest3\TapToDeferRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(TapToDeferRector::class);
};
