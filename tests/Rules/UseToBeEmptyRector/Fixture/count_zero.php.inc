<?php

expect(count($array))->toBe(0);
expect(sizeof($items))->toEqual(0);

?>
-----
<?php

expect($array)->toBeEmpty();
expect($items)->toBeEmpty();

?>
