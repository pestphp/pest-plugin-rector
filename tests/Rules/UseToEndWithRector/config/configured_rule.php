<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToEndWithRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToEndWithRector::class]);
