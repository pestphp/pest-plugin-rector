<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToEndWithRector;

return RectorConfig::configure()
    ->withRules([UseToEndWithRector::class]);
