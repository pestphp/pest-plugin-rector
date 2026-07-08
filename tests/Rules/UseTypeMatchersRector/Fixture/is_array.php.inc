<?php

test('converts is_array to toBeArray', function () {
    $value = [];

    expect(is_array($value))->toBeTrue();
});

?>
-----
<?php

test('converts is_array to toBeArray', function () {
    $value = [];

    expect($value)->toBeArray();
});

?>