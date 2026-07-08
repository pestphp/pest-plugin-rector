<?php

// Skip non-expect chains  
$value = array_key_exists('key', $arr);

// Skip non-toBeTrue methods
expect(array_key_exists('key', $arr))->toBeString();

// Skip wrong function
expect($value)->toBeTrue();
