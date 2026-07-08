<?php

expect(!$condition)->toBeTrue();
expect(!$value)->toBeFalse();
expect(!empty($array))->toBeTrue();
expect(!is_null($variable))->toBeTrue();

?>
-----
<?php

expect($condition)->toBeFalse();
expect($value)->toBeTrue();
expect(empty($array))->toBeFalse();
expect(is_null($variable))->toBeFalse();

?>
