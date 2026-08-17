<?php

/**
 * @var string $missing
 * @var string $path
 */
expect(is_file($path))->toBeTrue();
expect(is_file('/tmp/file.txt'))->toBeTrue();
expect(is_file($missing))->toBeFalse();

?>
-----
<?php

/**
 * @var string $missing
 * @var string $path
 */
expect($path)->toBeFile();
expect('/tmp/file.txt')->toBeFile();
expect($missing)->not->toBeFile();

?>
