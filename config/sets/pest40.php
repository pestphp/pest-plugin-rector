<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

/**
 * @see https://pestphp.com/docs/upgrade-guide
 *
 * Pest v4 primarily requires dependency updates (PHPUnit 12, PHP 8.3+)
 * with minimal code changes. Rules will be added as migration patterns emerge.
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');
};
