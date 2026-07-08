<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\SimplifyFilesystemMatchersRector;

return RectorConfig::configure()
    ->withRules([SimplifyFilesystemMatchersRector::class]);
