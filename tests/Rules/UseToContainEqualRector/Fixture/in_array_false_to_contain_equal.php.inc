<?php

expect(in_array($item, $array, false))->toBeTrue();
expect(in_array($item, $array, false))->toBeFalse();
expect(in_array('1', $numbers, false))->toBeTrue();

?>
-----
<?php

expect($array)->toContainEqual($item);
expect($array)->not->toContainEqual($item);
expect($numbers)->toContainEqual('1');

?>
