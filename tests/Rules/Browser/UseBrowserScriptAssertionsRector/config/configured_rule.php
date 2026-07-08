<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Browser\UseBrowserScriptAssertionsRector;

return RectorConfig::configure()
    ->withRules([UseBrowserScriptAssertionsRector::class]);
