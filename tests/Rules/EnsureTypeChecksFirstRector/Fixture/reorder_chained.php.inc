<?php

test('reorders chained type matcher', function () {
    $a = 10;

    expect($a)->toBe(10)->toBeInt();
});

-----
<?php

test('reorders chained type matcher', function () {
    $a = 10;

    expect($a)->toBeInt()->toBe(10);
});
