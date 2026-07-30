<?php

$value = 10;

expect($value)->toBe(10);
expect($value)->toBeInt();

?>
-----
<?php

$value = 10;

expect($value)->toBe(10)
    ->toBeInt();

?>
