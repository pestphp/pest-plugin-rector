<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToBeReadableWritableRector;

return RectorConfig::configure()
    ->withRules([UseToBeReadableWritableRector::class]);
