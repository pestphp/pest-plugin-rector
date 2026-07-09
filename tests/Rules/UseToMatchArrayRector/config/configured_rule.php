<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToMatchArrayRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseToMatchArrayRector::class);
};
