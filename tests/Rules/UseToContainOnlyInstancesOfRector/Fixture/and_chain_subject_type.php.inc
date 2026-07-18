<?php

test('skips mistyped and chain subject', function (array $root, string $tail) {
    expect($root)->toBeArray()
        ->and($tail)->each->toBeInstanceOf(Foo::class);
});

test('converts correctly typed and chain subject', function (array $root, array $tail) {
    expect($root)->toBeArray()
        ->and($tail)->each->toBeInstanceOf(Foo::class);
});

?>
-----
<?php

test('skips mistyped and chain subject', function (array $root, string $tail) {
    expect($root)->toBeArray()
        ->and($tail)->each->toBeInstanceOf(Foo::class);
});

test('converts correctly typed and chain subject', function (array $root, array $tail) {
    expect($root)->toBeArray()
        ->and($tail)->toContainOnlyInstancesOf(Foo::class);
});

?>
