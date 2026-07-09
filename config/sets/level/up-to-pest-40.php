<?php

declare(strict_types=1);

use Pest\Rector\Set\PestLevelSetList;
use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([PestSetList::PEST_40, PestLevelSetList::UP_TO_PEST_30]);
};
