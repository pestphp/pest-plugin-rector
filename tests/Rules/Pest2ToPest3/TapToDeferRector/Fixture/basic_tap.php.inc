<?php

test('replaces tap with defer', function () {
    $value = 10;

    expect($value)->tap(fn ($v) => dump($v))->toBe(10);
});

?>
-----
<?php

test('replaces tap with defer', function () {
    $value = 10;

    expect($value)->defer(fn ($v) => dump($v))->toBe(10);
});

?>
