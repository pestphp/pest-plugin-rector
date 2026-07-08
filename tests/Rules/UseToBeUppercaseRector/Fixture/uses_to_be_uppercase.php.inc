<?php

namespace Tests\Rules\UseToBeUppercaseRector\Fixture;

test('uses toBeUppercase', function () {
    $value = 'HELLO';

    expect(strtoupper($value) === $value)->toBeTrue();
    expect($value === strtoupper($value))->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseToBeUppercaseRector\Fixture;

test('uses toBeUppercase', function () {
    $value = 'HELLO';

    expect($value)->toBeUppercase();
    expect($value)->toBeUppercase();
});

?>
