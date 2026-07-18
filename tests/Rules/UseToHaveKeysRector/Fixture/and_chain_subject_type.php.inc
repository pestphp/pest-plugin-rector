<?php

// The type guard must read the `and()` subject, not the root expect() argument,
// or a well-typed head vouches for a tail of the wrong type.

test('skips mistyped and chain subject', function (array $root, string $tail) {
    expect($root)->toHaveKey('a')
        ->and($tail)->toHaveKey('a')->toHaveKey('b');
});

test('converts correctly typed and chain subject', function (array $root, array $tail) {
    expect($root)->toBeArray()
        ->and($tail)->toHaveKey('a')->toHaveKey('b');
});

?>
-----
<?php

// The type guard must read the `and()` subject, not the root expect() argument,
// or a well-typed head vouches for a tail of the wrong type.

test('skips mistyped and chain subject', function (array $root, string $tail) {
    expect($root)->toHaveKey('a')
        ->and($tail)->toHaveKey('a')->toHaveKey('b');
});

test('converts correctly typed and chain subject', function (array $root, array $tail) {
    expect($root)->toBeArray()
        ->and($tail)->toHaveKeys(['a', 'b']);
});

?>
