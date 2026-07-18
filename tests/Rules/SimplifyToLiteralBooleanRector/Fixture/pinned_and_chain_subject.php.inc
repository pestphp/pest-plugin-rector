<?php

// When the `and()` subject itself is well typed, the conversion is still safe.

test('converts pinned subjects in and chains', function (bool $flag, bool $other, array $list, string $text) {
    expect($flag)->toBeTrue()
        ->and($other)->toEqual(true);

    expect($flag)->toBeTrue()
        ->and($list)->toEqual([]);

    expect($flag)->toBeTrue()
        ->and($text)->toEqual('');
});

?>
-----
<?php

// When the `and()` subject itself is well typed, the conversion is still safe.

test('converts pinned subjects in and chains', function (bool $flag, bool $other, array $list, string $text) {
    expect($flag)->toBeTrue()
        ->and($other)->toBeTrue();

    expect($flag)->toBeTrue()
        ->and($list)->toBeEmpty();

    expect($flag)->toBeTrue()
        ->and($text)->toBeEmpty();
});

?>
