<?php

expect(boolval($value))->toBeTrue();
expect(boolval($value))->toBeFalse();

?>
-----
<?php

expect($value)->toBeTruthy();
expect($value)->toBeFalsy();

?>
