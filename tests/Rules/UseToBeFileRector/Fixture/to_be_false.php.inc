<?php

/**
 * @var string $path
 */
// Test toBeFalse pattern
expect(is_file($path))->toBeFalse();

-----
<?php

/**
 * @var string $path
 */
// Test toBeFalse pattern
expect($path)->not->toBeFile();
