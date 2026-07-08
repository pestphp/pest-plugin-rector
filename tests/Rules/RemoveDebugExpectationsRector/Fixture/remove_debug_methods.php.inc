<?php

expect($user)->dump()->toBeInstanceOf(User::class);

expect($value)->ray()->toBe(42);

expect($items)->dd()->toHaveCount(3);

?>
-----
<?php

expect($user)->toBeInstanceOf(User::class);

expect($value)->toBe(42);

expect($items)->toHaveCount(3);

?>
