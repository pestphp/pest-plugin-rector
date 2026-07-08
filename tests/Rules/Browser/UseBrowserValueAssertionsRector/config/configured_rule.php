<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Browser\UseBrowserValueAssertionsRector;

return RectorConfig::configure()
    ->withRules([UseBrowserValueAssertionsRector::class]);
