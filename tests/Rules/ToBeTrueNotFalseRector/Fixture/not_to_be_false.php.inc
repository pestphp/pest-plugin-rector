<?php

test('simplifies not toBeFalse to toBeTrue', function () {
    $value = true;

    expect($value)->not->toBeFalse();
});

?>
-----
<?php

test('simplifies not toBeFalse to toBeTrue', function () {
    $value = true;

    expect($value)->toBeTrue();
});

?>
