<?php

test('simplifies toEqual([]) to toBeEmpty()', function () {
    $array = [];

    expect($array)->toEqual([]);
});

?>
-----
<?php

test('simplifies toEqual([]) to toBeEmpty()', function () {
    $array = [];

    expect($array)->toBeEmpty();
});

?>
