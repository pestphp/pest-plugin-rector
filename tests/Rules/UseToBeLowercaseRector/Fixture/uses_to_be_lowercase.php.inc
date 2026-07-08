<?php

namespace Tests\Rules\UseToBeLowercaseRector\Fixture;

test('uses toBeLowercase', function () {
    $value = 'hello';

    expect(strtolower($value) === $value)->toBeTrue();
    expect($value === strtolower($value))->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseToBeLowercaseRector\Fixture;

test('uses toBeLowercase', function () {
    $value = 'hello';

    expect($value)->toBeLowercase();
    expect($value)->toBeLowercase();
});

?>
