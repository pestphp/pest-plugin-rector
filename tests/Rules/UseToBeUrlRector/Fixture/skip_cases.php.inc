<?php

namespace Tests\Rules\UseToBeUrlRector\Fixture;

test('skips lookalikes', function () {
    $url = 'https://example.com';
    $number = 5;

    // Skip other filters
    expect(filter_var($url, FILTER_VALIDATE_EMAIL))->not->toBeFalse();
    expect(filter_var($url, FILTER_VALIDATE_EMAIL) !== false)->toBeTrue();

    // Skip filter_var() without a filter argument
    expect(filter_var($url) !== false)->toBeTrue();

    // Skip toBeFalse without the not modifier
    expect(filter_var($url, FILTER_VALIDATE_URL))->toBeFalse();

    // Skip identical comparisons (=== false)
    expect(filter_var($url, FILTER_VALIDATE_URL) === false)->toBeTrue();

    // Skip comparisons against something other than false
    expect(filter_var($url, FILTER_VALIDATE_URL) !== null)->toBeTrue();

    // Skip reversed operands
    expect(false !== filter_var($url, FILTER_VALIDATE_URL))->toBeTrue();

    // Skip non-string subjects
    expect(filter_var($number, FILTER_VALIDATE_URL))->not->toBeFalse();
    expect(filter_var($number, FILTER_VALIDATE_URL) !== false)->toBeTrue();
});

?>
