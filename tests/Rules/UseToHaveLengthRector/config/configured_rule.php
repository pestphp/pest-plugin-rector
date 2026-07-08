<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToHaveLengthRector;

return RectorConfig::configure()
    ->withRules([UseToHaveLengthRector::class]);
