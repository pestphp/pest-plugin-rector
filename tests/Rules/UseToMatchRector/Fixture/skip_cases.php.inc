<?php

// Skip non-expect chains  
$value = preg_match('/test/', $str);

// Skip non-toBeTrue methods
expect(preg_match('/test/', $str))->toBeString();

// Skip wrong function
expect($value)->toBeTrue();
