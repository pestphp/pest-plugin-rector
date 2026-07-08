<?php

// Skip non-expect chains  
$value = str_ends_with($str, 'test');

// Skip non-toBeTrue methods
expect(str_ends_with($str, 'test'))->toBeString();

// Skip wrong function
expect($value)->toBeTrue();
