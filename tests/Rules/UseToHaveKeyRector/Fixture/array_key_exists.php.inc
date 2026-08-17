<?php

/**
 * @var array $array
 * @var array $data
 * @var array $user
 */
expect(array_key_exists('id', $array))->toBeTrue();
expect(array_key_exists($key, $data))->toBeTrue();
expect(array_key_exists('name', $user))->toBeFalse();

?>
-----
<?php

/**
 * @var array $array
 * @var array $data
 * @var array $user
 */
expect($array)->toHaveKey('id');
expect($data)->toHaveKey($key);
expect($user)->not->toHaveKey('name');

?>
