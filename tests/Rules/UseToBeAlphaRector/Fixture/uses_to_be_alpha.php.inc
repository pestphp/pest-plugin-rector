<?php

namespace Tests\Rules\UseToBeAlphaRector\Fixture;

test('uses toBeAlpha', function () {
    $value = 'HelloWorld';

    expect(ctype_alpha($value))->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseToBeAlphaRector\Fixture;

test('uses toBeAlpha', function () {
    $value = 'HelloWorld';

    expect($value)->toBeAlpha();
});

?>
