<?php

expect(str_starts_with($string, 'Hello'))->toBeTrue();
expect(str_starts_with($text, $prefix))->toBeTrue();
expect(str_starts_with($url, 'https://'))->toBeFalse();

?>
-----
<?php

expect($string)->toStartWith('Hello');
expect($text)->toStartWith($prefix);
expect($url)->not->toStartWith('https://');

?>
