<?php

/**
 * @var string $path
 */
// Test toBeFalse pattern
expect(is_readable($path))->toBeFalse();
expect(is_writable($path))->toBeFalse();

-----
<?php

/**
 * @var string $path
 */
// Test toBeFalse pattern
expect($path)->not->toBeReadable();
expect($path)->not->toBeWritable();
