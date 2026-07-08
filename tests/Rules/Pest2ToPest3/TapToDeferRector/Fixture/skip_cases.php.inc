<?php

// Skip non-expect chains
$result = someMethod()->tap(fn ($v) => dump($v))->other();

// Skip non-tap method names
expect($value)->toBe(10)->toBeInt();
