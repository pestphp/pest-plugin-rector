<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Browser\UseBrowserUrlAssertionsRector;

return RectorConfig::configure()
    ->withRules([UseBrowserUrlAssertionsRector::class]);
