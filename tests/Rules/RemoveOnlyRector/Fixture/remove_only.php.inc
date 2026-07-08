<?php

test('description', function () {
    expect(true)->toBeTrue();
})->only();

test('description', function ($email) {
    expect(true)->toBeTrue();
})->with('email')->only();

?>
-----
<?php

test('description', function () {
    expect(true)->toBeTrue();
});

test('description', function ($email) {
    expect(true)->toBeTrue();
})->with('email');

?>
