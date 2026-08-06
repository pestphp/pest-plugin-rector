<?php

$a = 10;
$b = 20;
$c = null;

expect($a)->toBe(10)->toBeInt()->and($b)->toBe(20)->and($c)->not->toBeInt();

?>
-----
<?php

$a = 10;
$b = 20;
$c = null;

expect($a)->toBe(10)->toBeInt();
expect($b)->toBe(20);
expect($c)->not->toBeInt();

?>
