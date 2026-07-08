<?php

// Skip non-expect chains
$result = each($array, fn ($v) => dump($v));

// Skip non-each methods
expect($array)->toBeArray();

// Skip when not expect argument
$value = 10;
