<?php

expect((bool) $value)->toBeTrue();
expect((bool) $value)->toBeFalse();

?>
-----
<?php

expect($value)->toBeTruthy();
expect($value)->toBeFalsy();

?>
