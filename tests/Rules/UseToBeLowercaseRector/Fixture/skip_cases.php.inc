<?php

namespace Tests\Rules\UseToBeLowercaseRector\Fixture;

test('skips lookalikes', function () {
    $value = 'Hello';
    $other = 'world';
    $number = 5;

    // Skip non-toBeTrue matchers
    expect(strtolower($value) === $value)->toBeFalse();

    // Skip non-expect chains
    check(strtolower($value) === $value)->toBeTrue();

    // Skip loose comparisons
    expect(strtolower($value) == $value)->toBeTrue();

    // Skip lookalike functions
    expect(mb_strtolower($value) === $value)->toBeTrue();
    expect(lcfirst($value) === $value)->toBeTrue();

    // Skip when the compared value differs from the strtolower() argument
    expect(strtolower($value) === $other)->toBeTrue();

    // Skip non-string subjects
    expect(strtolower($number) === $number)->toBeTrue();
});

?>
