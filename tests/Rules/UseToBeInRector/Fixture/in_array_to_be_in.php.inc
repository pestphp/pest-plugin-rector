<?php

expect(in_array($value, ['pending', 'active']))->toBeTrue();
expect(in_array($status, $allowedStatuses))->toBeTrue();
expect(in_array($value, ['pending', 'active']))->toBeFalse();
expect(in_array($value, $list, true))->toBeTrue();

?>
-----
<?php

expect($value)->toBeIn(['pending', 'active']);
expect($status)->toBeIn($allowedStatuses);
expect($value)->not->toBeIn(['pending', 'active']);
expect($value)->toBeIn($list);

?>
