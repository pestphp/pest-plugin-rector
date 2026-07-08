<?php

// 2 matchers — second goes on new line
test('two matchers', function () {
    expect($value)->toBeString();
    expect($value)->toBeGreaterThan('a');
});

// 3 matchers — all extras go on new lines
test('three matchers', function () {
    expect($counter)->toBe(10);
    expect($counter)->toBeInt();
    expect($counter)->toBeGreaterThan(5);
});

// ->not->toBeX() as first matcher (unit stays inline), extras on new lines
test('not modifier then positive matchers', function () {
    expect($name)->not->toBeNull();
    expect($name)->toBeString();
    expect($name)->toBeGreaterThan('a');
});

// already-inline chain extended with a new same-var matcher:
// original tokens are preserved verbatim; only the new node gets newline
test('already chained same var', function () {
    expect($title)->toBe('hello')->toBeString();
    expect($title)->toBeGreaterThan('a');
});

-----
<?php

// 2 matchers — second goes on new line
test('two matchers', function () {
    expect($value)->toBeString()
        ->toBeGreaterThan('a');
});

// 3 matchers — all extras go on new lines
test('three matchers', function () {
    expect($counter)->toBe(10)
        ->toBeInt()
        ->toBeGreaterThan(5);
});

// ->not->toBeX() as first matcher (unit stays inline), extras on new lines
test('not modifier then positive matchers', function () {
    expect($name)->not->toBeNull()
        ->toBeString()
        ->toBeGreaterThan('a');
});

// already-inline chain extended with a new same-var matcher:
// original tokens are preserved verbatim; only the new node gets newline
test('already chained same var', function () {
    expect($title)->toBe('hello')->toBeString()
        ->toBeGreaterThan('a');
});
