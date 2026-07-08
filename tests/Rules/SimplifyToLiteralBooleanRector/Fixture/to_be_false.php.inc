<?php

test('simplifies toBe(false) to toBeFalse()', function () {
    $value = false;

    expect($value)->toBe(false);
});

?>
-----
<?php

test('simplifies toBe(false) to toBeFalse()', function () {
    $value = false;

    expect($value)->toBeFalse();
});

?>
