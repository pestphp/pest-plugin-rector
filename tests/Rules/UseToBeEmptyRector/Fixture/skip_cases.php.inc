<?php

// Skip toHaveCount with non-zero arg
expect($array)->toHaveCount(5);

// Skip count with non-zero arg
expect(count($array))->toBe(3);

// Skip non-expect chains
$result = empty($value);
