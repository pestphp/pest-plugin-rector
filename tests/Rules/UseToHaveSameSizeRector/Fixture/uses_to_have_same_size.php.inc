<?php

namespace Tests\Rules\UseToHaveSameSizeRector\Fixture;

test('uses toHaveSameSize', function () {
    $array1 = [1, 2, 3];
    $array2 = [4, 5, 6];

    expect(count($array1))->toBe(count($array2));
    expect($array1)->toHaveCount(count($array2));
});

?>
-----
<?php

namespace Tests\Rules\UseToHaveSameSizeRector\Fixture;

test('uses toHaveSameSize', function () {
    $array1 = [1, 2, 3];
    $array2 = [4, 5, 6];

    expect($array1)->toHaveSameSize($array2);
    expect($array1)->toHaveSameSize($array2);
});

?>
