<?php

expect(ctype_alnum($value))->toBeFalse();
expect(ctype_alnum($value))->not->toBeTrue();

?>
-----
<?php

expect($value)->not->toBeAlphaNumeric();
expect($value)->not->toBeAlphaNumeric();

?>
