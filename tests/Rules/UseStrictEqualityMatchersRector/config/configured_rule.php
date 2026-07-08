<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseStrictEqualityMatchersRector;

return RectorConfig::configure()
    ->withRules([UseStrictEqualityMatchersRector::class]);
