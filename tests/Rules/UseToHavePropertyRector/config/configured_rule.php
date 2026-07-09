<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToHavePropertyRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToHavePropertyRector::class]);
