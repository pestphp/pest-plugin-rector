<?php

expect(in_array($item, $array))->toBeTrue();
expect(in_array($needle, $haystack, true))->toBeTrue();
expect(in_array('value', $list))->toBeTrue();

// toBeFalse should use ->not->toContain
expect(in_array($item, $array))->toBeFalse();

?>
-----
<?php

expect($array)->toContain($item);
expect($haystack)->toContain($needle);
expect($list)->toContain('value');

// toBeFalse should use ->not->toContain
expect($array)->not->toContain($item);

?>
