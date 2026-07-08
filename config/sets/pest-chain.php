<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\ChainExpectCallsRector;
use RectorPest\Rules\EnsureTypeChecksFirstRector;

/**
 * Pest expectation chaining rules
 *
 * This set contains rules for:
 * - Merging multiple expect() calls into chained expectations
 * - Optimizing the order of assertions in chains
 * - Formatting chained expectations for better readability
 *
 * These rules should typically run after other code quality rules
 * to maximize the opportunities for chaining.
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    // chained fluent calls only in files where it makes changes.
    $rectorConfig->rule(ChainExpectCallsRector::class);      // Merges separate expect() calls
    $rectorConfig->rule(EnsureTypeChecksFirstRector::class); // Reorders type checks within chains
};
