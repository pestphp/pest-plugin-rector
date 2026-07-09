<?php

declare(strict_types=1);

use Pest\Rector\Rules\SimplifyFilesystemMatchersRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([SimplifyFilesystemMatchersRector::class]);
