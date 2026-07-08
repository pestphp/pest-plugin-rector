<?php

$value1 = 10;
$value2 = 20;

expect($value1)->toBe(10);
expect($value2)->toBe(20);

?>

-----
<?php

$value1 = 10;
$value2 = 20;

expect($value1)->toBe(10)
    ->and($value2)->toBe(20);

?>

