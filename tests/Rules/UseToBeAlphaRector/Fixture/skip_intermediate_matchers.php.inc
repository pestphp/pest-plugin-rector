<?php

// Rebuilding the chain from the expect() holder would discard every matcher
// between the holder and the one being rewritten, silently dropping assertions.

test('skips chains with intermediate matchers', function (string $value) {
    expect(ctype_alpha($value))->toBeBool()->toBeTrue();
});

test('skips chains with several intermediate matchers', function (string $value) {
    expect(ctype_alpha($value))->toBeBool()->not->toBeNull()->toBeTrue();
});

test('still rewrites when applied directly', function (string $value) {
    expect(ctype_alpha($value))->toBeTrue();
});

?>
-----
<?php

// Rebuilding the chain from the expect() holder would discard every matcher
// between the holder and the one being rewritten, silently dropping assertions.

test('skips chains with intermediate matchers', function (string $value) {
    expect(ctype_alpha($value))->toBeBool()->toBeTrue();
});

test('skips chains with several intermediate matchers', function (string $value) {
    expect(ctype_alpha($value))->toBeBool()->not->toBeNull()->toBeTrue();
});

test('still rewrites when applied directly', function (string $value) {
    expect($value)->toBeAlpha();
});

?>
