<?php

// Regression: https://github.com/pestphp/pest/issues/1811
// A ->not modifier belongs to the matcher right after it, so reordering must
// move both together instead of hoisting every ->not to the front.
expect($foo)->not->toBe('baz')->not->toBeNull();
expect($foo)->toBe('baz')->not->toBeNull();
expect($foo)->not()->toBe('baz')->not()->toBeNull();

-----
<?php

// Regression: https://github.com/pestphp/pest/issues/1811
// A ->not modifier belongs to the matcher right after it, so reordering must
// move both together instead of hoisting every ->not to the front.
expect($foo)->not->toBeNull()->not->toBe('baz');
expect($foo)->not->toBeNull()->toBe('baz');
expect($foo)->not()->toBeNull()->not()->toBe('baz');
