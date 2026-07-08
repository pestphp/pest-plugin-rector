<?php

namespace Tests\Rules\UseToBeAlphaNumericRector\Fixture;

test('uses toBeAlphaNumeric', function () {
    $value = 'Hello123';

    expect(ctype_alnum($value))->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseToBeAlphaNumericRector\Fixture;

test('uses toBeAlphaNumeric', function () {
    $value = 'Hello123';

    expect($value)->toBeAlphaNumeric();
});

?>
