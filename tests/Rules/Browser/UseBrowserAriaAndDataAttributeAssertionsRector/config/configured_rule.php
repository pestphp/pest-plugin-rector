<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Browser\UseBrowserAriaAndDataAttributeAssertionsRector;

return RectorConfig::configure()
    ->withRules([UseBrowserAriaAndDataAttributeAssertionsRector::class]);
