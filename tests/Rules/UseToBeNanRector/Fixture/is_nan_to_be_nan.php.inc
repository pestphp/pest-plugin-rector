<?php

expect(is_nan($value))->toBeTrue();
expect(is_nan(sqrt(-1)))->toBeTrue();
expect(is_nan($value))->toBeFalse();

?>
-----
<?php

expect($value)->toBeNan();
expect(sqrt(-1))->toBeNan();
expect($value)->not->toBeNan();

?>
