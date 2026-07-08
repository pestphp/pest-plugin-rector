<?php

namespace Tests\Rules\UseToBeBetweenRector\Fixture;

test('uses toBeBetween', function () {
    $value = 5;
    $age = 25;

    expect($value >= 1 && $value <= 10)->toBeTrue();
    expect($age >= 18 && $age <= 65)->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseToBeBetweenRector\Fixture;

test('uses toBeBetween', function () {
    $value = 5;
    $age = 25;

    expect($value)->toBeBetween(1, 10);
    expect($age)->toBeBetween(18, 65);
});

?>
