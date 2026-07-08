<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToBeFileRector;

return RectorConfig::configure()
    ->withRules([UseToBeFileRector::class]);
