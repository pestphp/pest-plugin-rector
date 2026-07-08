<?php

expect(array_key_exists('id', $array))->toBeTrue();
expect(array_key_exists($key, $data))->toBeTrue();
expect(array_key_exists('name', $user))->toBeFalse();

?>
-----
<?php

expect($array)->toHaveKey('id');
expect($data)->toHaveKey($key);
expect($user)->not->toHaveKey('name');

?>
