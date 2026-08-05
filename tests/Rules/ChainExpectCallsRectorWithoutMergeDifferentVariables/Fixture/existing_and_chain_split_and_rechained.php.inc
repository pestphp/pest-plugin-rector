<?php

$a = 10;
$b = 20;

expect($a)->toBe(10)->and($b)->toBe(20)->not->toBeNull();
expect($b)->toBeInt();

?>
-----
<?php

$a = 10;
$b = 20;

expect($a)->toBe(10);
expect($b)->toBe(20)->not->toBeNull()
    ->toBeInt();

?>
