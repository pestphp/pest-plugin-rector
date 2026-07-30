<?php

$a = 10;
$b = 20;

expect($a)->toBe(10);
expect($a)->toBeInt();
expect($b)->toBe(20);
expect($b)->toBeInt();

?>
-----
<?php

$a = 10;
$b = 20;

expect($a)->toBe(10)
    ->toBeInt();
expect($b)->toBe(20)
    ->toBeInt();

?>
