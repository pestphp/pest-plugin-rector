<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToHaveKeysRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToHaveKeysRector::class);
};
