<?php

// Skip non-expect chains
$result = is_array($value);

// Skip non-toBeTrue methods
expect(is_array($value))->toBeFalse();

// Expect without function call argument
expect($value)->toBeTrue();

// Function with multiple arguments
expect(is_array($value, $extra))->toBeTrue();
