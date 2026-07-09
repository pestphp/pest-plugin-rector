<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToBeFileRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToBeFileRector::class]);
