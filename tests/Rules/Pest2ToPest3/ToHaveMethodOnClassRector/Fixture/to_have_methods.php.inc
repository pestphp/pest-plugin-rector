<?php

test('converts toHaveMethods to use class', function () {
    $user = new User();

    expect($user)->toHaveMethods(['getName', 'getEmail']);
});

?>
-----
<?php

test('converts toHaveMethods to use class', function () {
    $user = new User();

    expect($user::class)->toHaveMethods(['getName', 'getEmail']);
});

?>
