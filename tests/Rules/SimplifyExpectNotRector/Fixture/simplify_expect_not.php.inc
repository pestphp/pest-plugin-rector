<?php

$condition = random_int(0, 1) === 1;
$value = random_int(0, 1) === 1;

expect(!$condition)->toBeTrue();
expect(!$value)->toBeFalse();
expect(!empty($array))->toBeTrue();
expect(!is_null($variable))->toBeTrue();

?>
-----
<?php

$condition = random_int(0, 1) === 1;
$value = random_int(0, 1) === 1;

expect($condition)->toBeFalse();
expect($value)->toBeTrue();
expect(empty($array))->toBeFalse();
expect(is_null($variable))->toBeFalse();

?>
