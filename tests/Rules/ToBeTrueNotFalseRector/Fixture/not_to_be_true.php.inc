<?php

test('simplifies not toBeTrue to toBeFalse', function () {
    $value = false;

    expect($value)->not->toBeTrue();
});

?>
-----
<?php

test('simplifies not toBeTrue to toBeFalse', function () {
    $value = false;

    expect($value)->toBeFalse();
});

?>
