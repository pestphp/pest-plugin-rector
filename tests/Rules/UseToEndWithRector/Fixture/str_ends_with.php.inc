<?php

expect(str_ends_with($string, 'World'))->toBeTrue();
expect(str_ends_with($filename, '.php'))->toBeTrue();
expect(str_ends_with($path, '/'))->toBeFalse();

?>
-----
<?php

expect($string)->toEndWith('World');
expect($filename)->toEndWith('.php');
expect($path)->not->toEndWith('/');

?>
