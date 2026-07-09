<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToHaveLengthRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToHaveLengthRector::class]);
