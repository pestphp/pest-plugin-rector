<?php

test('counts', function () {
    $a = [1];
    $b = [1, 2];

    expect(count($a))->toBe(1);
    expect(count($b))->toBe(2);
});

?>
-----
<?php

test('counts', function () {
    $a = [1];
    $b = [1, 2];

    expect($a)->toHaveCount(1)
        ->and($b)->toHaveCount(2);
});

?>
