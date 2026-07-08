<?php

test('converts count() to toHaveCount()', function () {
    $array = [1, 2, 3];

    expect(count($array))->toBe(3);
});

?>
-----
<?php

test('converts count() to toHaveCount()', function () {
    $array = [1, 2, 3];

    expect($array)->toHaveCount(3);
});

?>