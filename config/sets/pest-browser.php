<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Browser\UseBrowserAriaAndDataAttributeAssertionsRector;
use RectorPest\Rules\Browser\UseBrowserAttributeAssertionsRector;
use RectorPest\Rules\Browser\UseBrowserScriptAssertionsRector;
use RectorPest\Rules\Browser\UseBrowserSourceAssertionsRector;
use RectorPest\Rules\Browser\UseBrowserUrlAssertionsRector;
use RectorPest\Rules\Browser\UseBrowserValueAssertionsRector;

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
    $rectorConfig->import(__DIR__ . '/../config.php');

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
