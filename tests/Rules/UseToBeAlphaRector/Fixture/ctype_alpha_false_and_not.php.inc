<?php

expect(ctype_alpha($value))->toBeFalse();
expect(ctype_alpha($value))->not->toBeTrue();

?>
-----
<?php

expect($value)->not->toBeAlpha();
expect($value)->not->toBeAlpha();

?>
