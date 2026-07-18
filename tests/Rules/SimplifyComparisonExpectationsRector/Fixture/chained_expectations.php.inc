<?php

namespace Tests\Rules\SimplifyComparisonExpectationsRector\Fixture;

test('simplifies comparisons inside and chains', function () {
    $count = 5;
    $price = 49;

    expect($count)->toBeInt()
        ->and($price > 10)->toBeTrue();

    expect($count)->toBeInt()
        ->and($price > 10)->toBeTrue()
        ->and($count <= 5)->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\SimplifyComparisonExpectationsRector\Fixture;

test('simplifies comparisons inside and chains', function () {
    $count = 5;
    $price = 49;

    expect($count)->toBeInt()
        ->and($price)->toBeGreaterThan(10);

    expect($count)->toBeInt()
        ->and($price)->toBeGreaterThan(10)
        ->and($count)->toBeLessThanOrEqual(5);
});

?>
