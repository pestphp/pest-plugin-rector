<?php

// Skip non-expect chains
$result = in_array($value, ['pending', 'active']);

// Skip non-toBeTrue/toBeFalse methods
expect(in_array($value, ['a']))->toBeString();

// Skip when arg count is wrong (only 1 arg)
expect(in_array($value))->toBeTrue();

// Skip loose comparisons — in_array() defaults to ==, toBeIn() compares with
// ===; these belong to UseToContainRector/UseToContainEqualRector
expect(in_array($value, ['pending', 'active']))->toBeTrue();
expect(in_array($status, $allowedStatuses))->toBeTrue();
expect(in_array($value, $list, false))->toBeTrue();
expect(in_array($value, $list, $strict))->toBeTrue();
