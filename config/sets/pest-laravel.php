<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\UseToBeCamelCaseRector;
use RectorPest\Rules\UseToBeKebabCaseRector;
use RectorPest\Rules\UseToBeSlugRector;
use RectorPest\Rules\UseToBeSnakeCaseRector;
use RectorPest\Rules\UseToBeStudlyCaseRector;

/**
 * Code quality improvements for Pest tests in Laravel projects
 *
 * This set requires illuminate/support (Laravel) and contains rules for:
 * - Converting Illuminate\Support\Str:: equality checks to Pest string case matchers
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    // String case matchers (requires illuminate/support)
    $rectorConfig->rule(UseToBeSnakeCaseRector::class);
    $rectorConfig->rule(UseToBeKebabCaseRector::class);
    $rectorConfig->rule(UseToBeCamelCaseRector::class);
    $rectorConfig->rule(UseToBeStudlyCaseRector::class);
    $rectorConfig->rule(UseToBeSlugRector::class);
};
