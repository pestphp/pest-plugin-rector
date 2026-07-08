<?php

// Test all comparison operators
expect($value > 10)->toBeTrue();
expect($value >= 10)->toBeTrue();
expect($value < 5)->toBeTrue();
expect($value <= 5)->toBeTrue();

-----
<?php

// Test all comparison operators
expect($value)->toBeGreaterThan(10);
expect($value)->toBeGreaterThanOrEqual(10);
expect($value)->toBeLessThan(5);
expect($value)->toBeLessThanOrEqual(5);
