<?php

/**
 * @var array $array
 * @var array $numbers
 */
expect(in_array($item, $array, false))->toBeTrue();
expect(in_array($item, $array, false))->toBeFalse();
expect(in_array('1', $numbers, false))->toBeTrue();

?>
-----
<?php

/**
 * @var array $array
 * @var array $numbers
 */
expect($array)->toContainEqual($item);
expect($array)->not->toContainEqual($item);
expect($numbers)->toContainEqual('1');

?>
