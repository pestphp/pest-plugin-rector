<?php

/**
 * @var float $value
 */
expect(is_infinite($value))->toBeTrue();
expect(is_infinite(log(0)))->toBeTrue();
expect(is_infinite($value))->toBeFalse();

?>
-----
<?php

/**
 * @var float $value
 */
expect($value)->toBeInfinite();
expect(log(0))->toBeInfinite();
expect($value)->not->toBeInfinite();

?>
