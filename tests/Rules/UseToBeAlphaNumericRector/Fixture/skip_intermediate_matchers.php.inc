<?php

test('skips chains with intermediate matchers', function (string $value) {
    expect(ctype_alnum($value))->toBeBool()->toBeTrue();
});

test('rewrites directly applied matchers, including through not', function (string $value, string $other) {
    expect(ctype_alnum($value))->toBeTrue();
    expect(ctype_alnum($other))->not->toBeTrue();
});

?>
-----
<?php

test('skips chains with intermediate matchers', function (string $value) {
    expect(ctype_alnum($value))->toBeBool()->toBeTrue();
});

test('rewrites directly applied matchers, including through not', function (string $value, string $other) {
    expect($value)->toBeAlphaNumeric();
    expect($other)->not->toBeAlphaNumeric();
});

?>
