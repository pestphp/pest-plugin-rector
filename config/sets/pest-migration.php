<?php

declare(strict_types=1);

use Pest\Rector\Rules\ConvertAssertToExpectRector;
use Pest\Rector\Rules\ConvertExpectExceptionToThrowRector;
use Rector\Config\RectorConfig;

/**
 * PHPUnit to Pest migration rules
 *
 * This set contains rules for converting PHPUnit test patterns to Pest equivalents.
 * These are structural transformations and should be reviewed after application.
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__.'/../config.php');

    // PHPUnit assertion to Pest expect() conversion
    $rectorConfig->rule(ConvertAssertToExpectRector::class);
    $rectorConfig->rule(ConvertExpectExceptionToThrowRector::class);
};
