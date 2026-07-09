<?php

declare(strict_types=1);

use Pest\Rector\Rules\UseToBeCamelCaseRector;
use Pest\Rector\Rules\UseToBeKebabCaseRector;
use Pest\Rector\Rules\UseToBeSlugRector;
use Pest\Rector\Rules\UseToBeSnakeCaseRector;
use Pest\Rector\Rules\UseToBeStudlyCaseRector;
use Rector\Config\RectorConfig;

/**
 * Code quality improvements for Pest tests in Laravel projects
 *
 * This set requires illuminate/support (Laravel) and contains rules for:
 * - Converting Illuminate\Support\Str:: equality checks to Pest string case matchers
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__.'/../config.php');

    // String case matchers (requires illuminate/support)
    $rectorConfig->rule(UseToBeSnakeCaseRector::class);
    $rectorConfig->rule(UseToBeKebabCaseRector::class);
    $rectorConfig->rule(UseToBeCamelCaseRector::class);
    $rectorConfig->rule(UseToBeStudlyCaseRector::class);
    $rectorConfig->rule(UseToBeSlugRector::class);
};
