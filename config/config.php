<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

/**
 * Base configuration for rector-pest
 * Import this in your set configurations
 */
return static function (RectorConfig $rectorConfig): void {
    $configPath = "vendor" . DIRECTORY_SEPARATOR . "pestphp" . DIRECTORY_SEPARATOR . "pest-plugin-phpstan" . DIRECTORY_SEPARATOR . "extension.neon";

    if (file_exists($configPath)) {
        $rectorConfig->phpstanConfig($configPath);
    }
};
