<?php

expect(ctype_digit($value))->toBeTrue();
expect(ctype_digit('12345'))->toBeTrue();
expect(ctype_digit($value))->toBeFalse();

?>
-----
<?php

expect($value)->toBeDigits();
expect('12345')->toBeDigits();
expect($value)->not->toBeDigits();

?>
