<?php

declare(strict_types=1);

use Pest\Rector\Rules\Browser\UseBrowserAriaAndDataAttributeAssertionsRector;
use Pest\Rector\Rules\Browser\UseBrowserAttributeAssertionsRector;
use Pest\Rector\Rules\Browser\UseBrowserScriptAssertionsRector;
use Pest\Rector\Rules\Browser\UseBrowserSourceAssertionsRector;
use Pest\Rector\Rules\Browser\UseBrowserUrlAssertionsRector;
use Pest\Rector\Rules\Browser\UseBrowserValueAssertionsRector;
use Rector\Config\RectorConfig;

/**
 * Code quality improvements for Pest Browser tests
 *
 * Requires pestphp/pest-plugin-browser to be installed in the target project.
 *
 * This set converts generic expect($page->getter())->matcher() patterns into
 * the dedicated browser assertion methods provided by the plugin, resulting in
 * more readable tests and clearer failure messages.
 */
return static function (RectorConfig $rectorConfig): void {
    // Import shared Rector configuration (PHP version, parallel settings, etc.)
    $rectorConfig->import(__DIR__.'/../config.php');

    // Value assertions
    $rectorConfig->rule(UseBrowserValueAssertionsRector::class);

    // Aria and data attribute assertions (more specific — must run before general attribute rule)
    $rectorConfig->rule(UseBrowserAriaAndDataAttributeAssertionsRector::class);

    // Attribute assertions
    $rectorConfig->rule(UseBrowserAttributeAssertionsRector::class);

    // Source / content assertions
    $rectorConfig->rule(UseBrowserSourceAssertionsRector::class);

    // Script assertions
    $rectorConfig->rule(UseBrowserScriptAssertionsRector::class);

    // URL assertions
    $rectorConfig->rule(UseBrowserUrlAssertionsRector::class);
};
