<?php

test('converts greater than comparison', function () {
    $value = 15;

    expect($value > 10)->toBeTrue();
});

?>
-----
<?php

test('converts greater than comparison', function () {
    $value = 15;

    expect($value)->toBeGreaterThan(10);
});

?>