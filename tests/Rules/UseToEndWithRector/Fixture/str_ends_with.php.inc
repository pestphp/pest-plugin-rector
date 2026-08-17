<?php

/**
 * @var string $filename
 * @var string $path
 * @var string $string
 */
expect(str_ends_with($string, 'World'))->toBeTrue();
expect(str_ends_with($filename, '.php'))->toBeTrue();
expect(str_ends_with($path, '/'))->toBeFalse();

?>
-----
<?php

/**
 * @var string $filename
 * @var string $path
 * @var string $string
 */
expect($string)->toEndWith('World');
expect($filename)->toEndWith('.php');
expect($path)->not->toEndWith('/');

?>
