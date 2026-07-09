<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToStartWithRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToStartWithRector::class]);
