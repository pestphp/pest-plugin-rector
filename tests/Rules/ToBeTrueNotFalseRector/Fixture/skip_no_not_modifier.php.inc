<?php

test('skips regular toBeTrue', function () {
    $value = true;

    expect($value)->toBeTrue();

    expect($value)->not->toBeNull();
    expect($value)->not->toBeEmpty();
});

?>
