<?php

declare(strict_types=1);

use Pest\Rector\Rules\Browser\UseBrowserUrlAssertionsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseBrowserUrlAssertionsRector::class]);
