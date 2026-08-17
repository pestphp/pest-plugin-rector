<?php

/**
 * @var array $items
 * @var string $value
 */
expect(empty($value))->toBeTrue();
expect(empty($items))->toBeFalse();

?>
-----
<?php

/**
 * @var array $items
 * @var string $value
 */
expect($value)->toBeEmpty();
expect($items)->not->toBeEmpty();

?>
