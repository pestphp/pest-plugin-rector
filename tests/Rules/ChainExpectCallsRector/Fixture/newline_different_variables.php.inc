<?php

// 3 vars × 1 matcher each — every ->and() on its own line
test('three vars one matcher each', function () {
    expect($a)->toBe(1);
    expect($b)->toBe(2);
    expect($c)->toBe(3);
});

// 3 vars × 2 matchers each — ->and() and extra-matcher newlines combined
test('three vars two matchers each', function () {
    expect($x)->toBe(1);
    expect($x)->toBeInt();
    expect($y)->toBe('hello');
    expect($y)->toBeString();
    expect($z)->toBeFloat();
    expect($z)->toBeGreaterThan(0.0);
});

// already-chained first statement + a new different variable:
// original tokens are preserved verbatim; only the new ->and() gets newline
test('already chained plus new var', function () {
    expect($first)->toBe(10)->toBeInt();
    expect($second)->toBeString();
});

-----
<?php

// 3 vars × 1 matcher each — every ->and() on its own line
test('three vars one matcher each', function () {
    expect($a)->toBe(1)
        ->and($b)->toBe(2)
        ->and($c)->toBe(3);
});

// 3 vars × 2 matchers each — ->and() and extra-matcher newlines combined
test('three vars two matchers each', function () {
    expect($x)->toBe(1)
        ->toBeInt()
        ->and($y)->toBe('hello')
        ->toBeString()
        ->and($z)->toBeFloat()
        ->toBeGreaterThan(0.0);
});

// already-chained first statement + a new different variable:
// original tokens are preserved verbatim; only the new ->and() gets newline
test('already chained plus new var', function () {
    expect($first)->toBe(10)->toBeInt()
        ->and($second)->toBeString();
});
