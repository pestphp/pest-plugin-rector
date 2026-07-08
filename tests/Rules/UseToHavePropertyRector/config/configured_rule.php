<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToHavePropertyRector;

return RectorConfig::configure()
    ->withRules([UseToHavePropertyRector::class]);
