<?php

test('converts is_string to toBeString', function () {
    $value = "hello";

    expect(is_string($value))->toBeTrue();
});

?>
-----
<?php

test('converts is_string to toBeString', function () {
    $value = "hello";

    expect($value)->toBeString();
});

?>