<?php

expect(empty($value))->toBeTrue();
expect(empty($items))->toBeFalse();

?>
-----
<?php

expect($value)->toBeEmpty();
expect($items)->not->toBeEmpty();

?>
