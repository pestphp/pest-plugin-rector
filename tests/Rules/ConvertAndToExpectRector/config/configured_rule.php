<?php

declare(strict_types=1);

use Pest\Rector\Rules\ConvertAndToExpectRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ConvertAndToExpectRector::class);
};
