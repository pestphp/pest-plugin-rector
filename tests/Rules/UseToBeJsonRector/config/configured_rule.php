<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToBeJsonRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToBeJsonRector::class]);
