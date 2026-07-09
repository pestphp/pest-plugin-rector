<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseStrictEqualityMatchersRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseStrictEqualityMatchersRector::class]);
