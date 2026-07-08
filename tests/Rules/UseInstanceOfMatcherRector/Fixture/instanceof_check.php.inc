<?php

test('converts instanceof to toBeInstanceOf', function () {
    $user = new User();

    expect($user instanceof User)->toBeTrue();
});

?>
-----
<?php

test('converts instanceof to toBeInstanceOf', function () {
    $user = new User();

    expect($user)->toBeInstanceOf(User::class);
});

?>