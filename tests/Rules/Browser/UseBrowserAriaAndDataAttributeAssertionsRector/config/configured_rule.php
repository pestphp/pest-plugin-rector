<?php

declare(strict_types=1);

use Pest\Rector\Rules\Browser\UseBrowserAriaAndDataAttributeAssertionsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseBrowserAriaAndDataAttributeAssertionsRector::class]);
