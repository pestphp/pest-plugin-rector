<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToHaveKeyRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToHaveKeyRector::class]);
