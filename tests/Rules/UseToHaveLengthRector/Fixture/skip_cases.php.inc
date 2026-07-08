<?php

// Skip non-expect chains  
$value = strlen($str);

// Skip non-toBe methods
expect(strlen($str))->toBeString();

// Skip wrong function
expect($value)->toBe(5);
