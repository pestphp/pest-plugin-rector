<?php

namespace Tests\Rules\UseToBeBetweenRector\Fixture;

test('skips lookalikes', function () {
    $value = 5;
    $other = 7;
    $name = 'pest';

    // Skip non-toBeTrue matchers
    expect($value >= 1 && $value <= 10)->toBeFalse();

    // Skip non-expect chains
    check($value >= 1 && $value <= 10)->toBeTrue();

    // Skip || instead of &&
    expect($value >= 1 || $value <= 10)->toBeTrue();

    // Skip strict comparisons (> / < instead of >= / <=)
    expect($value > 1 && $value < 10)->toBeTrue();

    // Skip reversed operand order (<= before >=)
    expect($value <= 10 && $value >= 1)->toBeTrue();

    // Skip when each side compares a different variable
    expect($value >= 1 && $other <= 10)->toBeTrue();

    // Skip non-numeric subjects
    expect($name >= 'a' && $name <= 'z')->toBeTrue();
});

?>
