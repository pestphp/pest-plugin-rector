<?php

test('simplifies toBe(null) to toBeNull()', function () {
    $value = null;

    expect($value)->toBe(null);
});

?>
-----
<?php

test('simplifies toBe(null) to toBeNull()', function () {
    $value = null;

    expect($value)->toBeNull();
});

?>
