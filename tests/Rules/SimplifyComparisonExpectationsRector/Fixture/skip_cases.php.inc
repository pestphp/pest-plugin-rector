<?php

// Skip non-expect chains
$result = ($value > 10);

// Skip non-toBeTrue methods
expect($value > 10)->toBeFalse();

// Skip non-comparison expressions
expect($value)->toBeTrue();
expect(is_array($value))->toBeTrue();
