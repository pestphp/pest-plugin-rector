<?php

test('converts preg_match to toMatch', function () {
    $string = "hello world";

    expect(preg_match('/hello/', $string))->toBe(1);
});

?>
-----
<?php

test('converts preg_match to toMatch', function () {
    $string = "hello world";

    expect($string)->toMatch('/hello/');
});

?>