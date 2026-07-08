<?php

// Skip non-expect chains
$result = count($array);

// Skip non-toBe/toEqual methods
expect(count($array))->toBeArray();

// Skip when method has wrong number of args
expect(count($array))->toBe(1, 2);

// Skip non-count functions
expect(strlen($str))->toBe(5);

// Count with multiple args
expect(count($array, COUNT_RECURSIVE))->toBe(5);
