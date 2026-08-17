<?php

/**
 * @var string $missing
 * @var string $path
 */
expect(is_dir($path))->toBeTrue();
expect(is_dir('/tmp'))->toBeTrue();
expect(is_dir($missing))->toBeFalse();

?>
-----
<?php

/**
 * @var string $missing
 * @var string $path
 */
expect($path)->toBeDirectory();
expect('/tmp')->toBeDirectory();
expect($missing)->not->toBeDirectory();

?>
