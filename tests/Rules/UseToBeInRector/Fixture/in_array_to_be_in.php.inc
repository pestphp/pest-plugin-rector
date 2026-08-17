<?php

/**
 * @var array $allowedStatuses
 * @var array $list
 */
expect(in_array($value, ['pending', 'active'], true))->toBeTrue();
expect(in_array($status, $allowedStatuses, true))->toBeTrue();
expect(in_array($value, ['pending', 'active'], true))->toBeFalse();
expect(in_array($value, $list, true))->toBeTrue();

?>
-----
<?php

/**
 * @var array $allowedStatuses
 * @var array $list
 */
expect($value)->toBeIn(['pending', 'active']);
expect($status)->toBeIn($allowedStatuses);
expect($value)->not->toBeIn(['pending', 'active']);
expect($value)->toBeIn($list);

?>
