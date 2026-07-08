<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Set\PestSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([PestSetList::PEST_30]);
};
