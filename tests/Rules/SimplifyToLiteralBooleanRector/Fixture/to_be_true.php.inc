<?php

test('simplifies toBe(true) to toBeTrue()', function () {
    $value = true;

    expect($value)->toBe(true);
});

?>
-----
<?php

test('simplifies toBe(true) to toBeTrue()', function () {
    $value = true;

    expect($value)->toBeTrue();
});

?>
