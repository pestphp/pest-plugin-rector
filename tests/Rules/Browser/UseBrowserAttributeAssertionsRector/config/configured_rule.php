<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Browser\UseBrowserAttributeAssertionsRector;

return RectorConfig::configure()
    ->withRules([UseBrowserAttributeAssertionsRector::class]);
