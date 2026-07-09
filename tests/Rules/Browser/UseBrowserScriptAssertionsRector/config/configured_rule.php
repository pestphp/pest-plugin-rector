<?php

declare(strict_types=1);

use Pest\Rector\Rules\Browser\UseBrowserScriptAssertionsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseBrowserScriptAssertionsRector::class]);
