<?php

expect(array_is_list($array))->toBeTrue();
expect(array_is_list([1, 2, 3]))->toBeTrue();
expect(array_is_list($array))->toBeFalse();

?>
-----
<?php

expect($array)->toBeList();
expect([1, 2, 3])->toBeList();
expect($array)->not->toBeList();

?>
