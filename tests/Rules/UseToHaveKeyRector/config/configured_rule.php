<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToHaveKeyRector;

return RectorConfig::configure()
    ->withRules([UseToHaveKeyRector::class]);
