<?php

test('converts toHaveMethod to use class', function () {
    $user = new User();

    expect($user)->toHaveMethod('getName');
});

?>
-----
<?php

test('converts toHaveMethod to use class', function () {
    $user = new User();

    expect($user::class)->toHaveMethod('getName');
});

?>
