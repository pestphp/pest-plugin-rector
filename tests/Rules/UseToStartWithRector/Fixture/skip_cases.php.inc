<?php

// Skip non-expect chains  
$value = str_starts_with($str, 'test');

// Skip non-toBeTrue methods
expect(str_starts_with($str, 'test'))->toBeString();

// Skip wrong function
expect($value)->toBeTrue();
