<?php

test('converts less than comparison', function () {
    $value = 3;

    expect($value < 5)->toBeTrue();
});

?>
-----
<?php

test('converts less than comparison', function () {
    $value = 3;

    expect($value)->toBeLessThan(5);
});

?>