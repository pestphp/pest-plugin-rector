<?php

expect($user)->dump()->toBeInstanceOf(User::class)->and($user->name)->dump()->toBe('John');

expect($value)->ray()->not->toBeNull();

?>
-----
<?php

expect($user)->toBeInstanceOf(User::class)->and($user->name)->toBe('John');

expect($value)->not->toBeNull();

?>
