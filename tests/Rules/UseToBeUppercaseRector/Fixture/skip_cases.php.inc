<?php

namespace Tests\Rules\UseToBeUppercaseRector\Fixture;

test('skips lookalikes', function () {
    $value = 'Hello';
    $other = 'WORLD';
    $number = 5;

    // Skip non-toBeTrue matchers
    expect(strtoupper($value) === $value)->toBeFalse();

    // Skip non-expect chains
    check(strtoupper($value) === $value)->toBeTrue();

    // Skip loose comparisons
    expect(strtoupper($value) == $value)->toBeTrue();

    // Skip lookalike functions
    expect(mb_strtoupper($value) === $value)->toBeTrue();
    expect(ucfirst($value) === $value)->toBeTrue();

    // Skip when the compared value differs from the strtoupper() argument
    expect(strtoupper($value) === $other)->toBeTrue();

    // Skip non-string subjects
    expect(strtoupper($number) === $number)->toBeTrue();
});

?>
