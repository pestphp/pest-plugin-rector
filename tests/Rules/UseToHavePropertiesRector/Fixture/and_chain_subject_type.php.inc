<?php

test('skips mistyped and chain subject', function (object $root, string $tail) {
    expect($root)->toHaveProperty('a')
        ->and($tail)->toHaveProperty('a')->toHaveProperty('b');
});

test('converts correctly typed and chain subject', function (object $root, object $tail) {
    expect($root)->toBeObject()
        ->and($tail)->toHaveProperty('a')->toHaveProperty('b');
});

?>
-----
<?php

test('skips mistyped and chain subject', function (object $root, string $tail) {
    expect($root)->toHaveProperty('a')
        ->and($tail)->toHaveProperty('a')->toHaveProperty('b');
});

test('converts correctly typed and chain subject', function (object $root, object $tail) {
    expect($root)->toBeObject()
        ->and($tail)->toHaveProperties(['a', 'b']);
});

?>
