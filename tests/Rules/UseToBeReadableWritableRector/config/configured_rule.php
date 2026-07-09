<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToBeReadableWritableRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToBeReadableWritableRector::class]);
