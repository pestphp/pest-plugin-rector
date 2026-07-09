<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToBeDirectoryRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToBeDirectoryRector::class]);
