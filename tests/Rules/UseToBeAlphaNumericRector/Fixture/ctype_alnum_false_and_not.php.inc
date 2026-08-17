<?php

/**
 * @var string $value
 */
expect(ctype_alnum($value))->toBeFalse();
expect(ctype_alnum($value))->not->toBeTrue();

?>
-----
<?php

/**
 * @var string $value
 */
expect($value)->not->toBeAlphaNumeric();
expect($value)->not->toBeAlphaNumeric();

?>
