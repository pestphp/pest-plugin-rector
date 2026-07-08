<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToStartWithRector;

return RectorConfig::configure()
    ->withRules([UseToStartWithRector::class]);
