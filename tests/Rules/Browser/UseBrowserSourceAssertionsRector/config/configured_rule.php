<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Browser\UseBrowserSourceAssertionsRector;

return RectorConfig::configure()
    ->withRules([UseBrowserSourceAssertionsRector::class]);
