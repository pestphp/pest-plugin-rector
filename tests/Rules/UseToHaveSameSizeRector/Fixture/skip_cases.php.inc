<?php

namespace Tests\Rules\UseToHaveSameSizeRector\Fixture;

test('skips lookalikes', function () {
    $array1 = [1, 2, 3];
    $array2 = [4, 5, 6];
    $word = 'pest';

    // Skip non-expect chains
    check(count($array1))->toBe(count($array2));

    // Skip non toBe/toEqual/toHaveCount matchers
    expect(count($array1))->toBeGreaterThan(count($array2));

    // Skip literal counts
    expect(count($array1))->toBe(3);
    expect($array1)->toHaveCount(3);

    // Skip when the expectation subject is not a count
    expect($array1)->toBe(count($array2));

    // Skip lookalike size functions
    expect(strlen($word))->toBe(strlen($word));

    // Skip when the subject is not an array or iterable
    expect($word)->toHaveCount(count($array2));
});

?>
