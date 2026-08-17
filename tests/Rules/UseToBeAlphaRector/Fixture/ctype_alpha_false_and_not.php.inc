<?php

/**
 * @var string $value
 */
expect(ctype_alpha($value))->toBeFalse();
expect(ctype_alpha($value))->not->toBeTrue();

?>
-----
<?php

/**
 * @var string $value
 */
expect($value)->not->toBeAlpha();
expect($value)->not->toBeAlpha();

?>
