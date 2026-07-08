<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToBeJsonRector;

return RectorConfig::configure()
    ->withRules([UseToBeJsonRector::class]);
