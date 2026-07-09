<?php

declare(strict_types=1);

use Pest\Rector\Rules\Browser\UseBrowserAttributeAssertionsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseBrowserAttributeAssertionsRector::class]);
