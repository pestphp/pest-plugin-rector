<?php

namespace Tests\Rules\ToBeTrueNotFalseRector\Fixture;

test('simplifies double negatives inside and chains', function () {
    $count = 1;
    $other = false;

    expect($count)->toBeInt()
        ->and($other)->not->toBeFalse();
});

test('preserves earlier not modifiers in the chain', function () {
    $flag = true;
    $other = false;

    expect($flag)->not->toBeNull()
        ->and($other)->not->toBeFalse();
});

?>
-----
<?php

namespace Tests\Rules\ToBeTrueNotFalseRector\Fixture;

test('simplifies double negatives inside and chains', function () {
    $count = 1;
    $other = false;

    expect($count)->toBeInt()
        ->and($other)->toBeTrue();
});

test('preserves earlier not modifiers in the chain', function () {
    $flag = true;
    $other = false;

    expect($flag)->not->toBeNull()
        ->and($other)->toBeTrue();
});

?>
