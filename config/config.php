<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

/**
 * Base configuration for rector-pest
 * Import this in your set configurations
 */
return static function (RectorConfig $rectorConfig): void {
    // When the Pest PHPStan plugin is installed in the consuming project, wire
    // it into Rector's PHPStan container so expectation value types resolve better.
    $configPath = getcwd().DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'pestphp'.DIRECTORY_SEPARATOR.'pest-plugin-phpstan'.DIRECTORY_SEPARATOR.'extension.neon';

    if (file_exists($configPath)) {
        $rectorConfig->phpstanConfig($configPath);
    }
};
