<?php

// Skip non-expect chains
$value = $item->method();

// Skip explicit loose checks handled by UseToContainEqualRector
expect(in_array($item, $array, false))->toBeTrue();
expect(in_array($item, $array, false))->toBeFalse();

// Skip non-expect toBe target
expect($value)->toBeString();
