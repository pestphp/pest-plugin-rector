<?php

// Test: start with expect() having no args or variadic args
expect()->toStartWith('x');
expect(...)->toStartWith('x');

// Test: dynamic method name on expect chain
expect('x')->$method('x');

// Test: expect(func()) where func is variable
$f = 'str_starts_with';
expect($f('haystack', 'needle'))->toBeTrue();
