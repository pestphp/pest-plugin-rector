<?php

// "every item is not a Foo" is not the same as "not (every item is a Foo)" —
// the two differ for any mixed array, so negated chains must be left alone.

test('skips negated each chains', function (array $items) {
    expect($items)->not->each->toBeInstanceOf(Foo::class);
});

test('skips negated each chains inside and', function (array $first, array $second) {
    expect($first)->toBeArray()
        ->and($second)->not->each->toBeInstanceOf(Foo::class);
});

?>
