<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToBeDirectoryRector;

return RectorConfig::configure()
    ->withRules([UseToBeDirectoryRector::class]);
