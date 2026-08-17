<?php

/**
 * @var string $path
 */
// Test toBeFalse pattern
expect(is_dir($path))->toBeFalse();

-----
<?php

/**
 * @var string $path
 */
// Test toBeFalse pattern
expect($path)->not->toBeDirectory();
