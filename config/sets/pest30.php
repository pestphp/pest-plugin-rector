<?php

declare(strict_types=1);

use Pest\Rector\Rules\Pest2ToPest3\TapToDeferRector;
use Pest\Rector\Rules\Pest2ToPest3\ToHaveMethodOnClassRector;
use Pest\Rector\Rules\Pest2ToPest3\UsesToExtendRector;
use Rector\Config\RectorConfig;

/**
 * @see https://pestphp.com/docs/upgrade-guide#content-from-pest-v2-to-pest-v3
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__.'/../config.php');

    $rectorConfig->rule(TapToDeferRector::class);

    $rectorConfig->rule(ToHaveMethodOnClassRector::class);

    $rectorConfig->rule(UsesToExtendRector::class);
};
