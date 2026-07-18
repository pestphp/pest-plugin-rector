<?php

// Skip non-expect chains  
$value = (true === true);

// Skip non-toBeTrue/toBeFalse methods
expect(true)->toBeString();

// Skip loose comparisons whose subject type is not pinned down —
// toEqual(true) passes for 1, toBeTrue() would not
expect($input)->toEqual(true);
expect($input)->toEqual(false);
expect($input)->toEqual(null);
expect(getMixedValue())->toEqual([]);

// Skip toBe('')/toBe([]) when the subject could be any empty value
expect($input)->toBe('');
expect($input)->toBe([]);
