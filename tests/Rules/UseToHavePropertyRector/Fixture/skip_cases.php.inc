<?php

// Skip non-expect chains  
$value = property_exists($obj, 'name');

// Skip non-toBeTrue methods
expect(property_exists($obj, 'name'))->toBeString();

// Skip wrong function
expect($value)->toBeTrue();
