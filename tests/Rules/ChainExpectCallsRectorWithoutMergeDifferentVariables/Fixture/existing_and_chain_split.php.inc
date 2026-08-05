<?php

$a = 10;
$b = 20;

expect($a)->toBe(10)->and($b)->toBe(20);
expect($a)->toBeInt();

?>
-----
<?php

$a = 10;
$b = 20;

expect($a)->toBe(10);
expect($b)->toBe(20);
expect($a)->toBeInt();

?>
