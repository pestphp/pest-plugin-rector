<?php

test('edgecases: instanceOf and not modifier', function () {
    $a = new stdClass();

    expect($a)->toBe(10)->toBeInstanceOf(stdClass::class);
    expect($a)->toBe(1)->not->toBeInt();
    expect($a)->toBe(1)->not()->toBeInt();
    $b = 1;
    expect($b)->toBe(1)->toBeInt()->toBeString();
});

-----
<?php

test('edgecases: instanceOf and not modifier', function () {
    $a = new stdClass();

    expect($a)->toBeInstanceOf(stdClass::class)->toBe(10);
    expect($a)->not->toBeInt()->toBe(1);
    expect($a)->not()->toBeInt()->toBe(1);
    $b = 1;
    expect($b)->toBeInt()->toBeString()->toBe(1);
});
