<?php

declare(strict_types=1);

use Pest\Rector\Rules\Browser\UseBrowserValueAssertionsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseBrowserValueAssertionsRector::class]);
