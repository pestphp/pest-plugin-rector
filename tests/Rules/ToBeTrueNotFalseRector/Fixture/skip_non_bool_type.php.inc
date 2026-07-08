<?php

test('skips non-boolean types', function () {
    $value = 'some string';

    expect($value)->not->toBeFalse();

    expect($value)->not->toBeTrue();
});

?>
