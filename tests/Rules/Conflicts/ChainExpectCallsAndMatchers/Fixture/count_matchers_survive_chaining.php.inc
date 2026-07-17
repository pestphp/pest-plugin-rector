<?php

test('counts', function () {
    expect(count($a))->toBe(1);
    expect(count($b))->toBe(2);
});

?>
-----
<?php

test('counts', function () {
    expect($a)->toHaveCount(1)
        ->and($b)->toHaveCount(2);
});

?>
