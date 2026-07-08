<?php

// Skip non-expect chains
$result = someMethod()->toHaveMethod('test');

// Skip non-toHaveMethod methods
expect($user)->toBe('test');

// Already has ::class - should not transform
expect($user::class)->toHaveMethod('getName');
expect(SomeClass::class)->toHaveMethod('test');
