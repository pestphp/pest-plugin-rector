<?php

// The type guard must read the `and()` subject, not the root expect() argument.
// Reading the root would let a well-typed head vouch for an unpinned tail.

test('skips unpinned boolean subject in and chain', function (bool $flag, mixed $value) {
    expect($flag)->toBeTrue()
        ->and($value)->toEqual(true);
});

test('skips unpinned array subject in and chain', function (array $list, mixed $value) {
    expect($list)->toBe([1])
        ->and($value)->toEqual([]);
});

test('skips unpinned string subject in and chain', function (string $text, mixed $value) {
    expect($text)->toBe('a')
        ->and($value)->toEqual('');
});

?>
