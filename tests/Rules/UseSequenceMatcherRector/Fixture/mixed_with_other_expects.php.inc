<?php

expect($users[0])->toBeInstanceOf(User::class);
expect($users[1])->toBeInstanceOf(Admin::class);
expect($users[2])->toBeInstanceOf(Guest::class);
expect($other)->toBeTrue();

?>
-----
<?php

expect($users)->sequence(fn($e) => $e->toBeInstanceOf(User::class), fn($e) => $e->toBeInstanceOf(Admin::class), fn($e) => $e->toBeInstanceOf(Guest::class));
expect($other)->toBeTrue();

?>
