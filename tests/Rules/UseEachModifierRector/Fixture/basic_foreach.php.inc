<?php

test('converts foreach with expect to each modifier', function () {
    $items = ['a', 'b', 'c'];

    foreach ($items as $item) {
        expect($item)->toBeString();
    }
});

?>
-----
<?php

test('converts foreach with expect to each modifier', function () {
    $items = ['a', 'b', 'c'];

    expect($items)->each->toBeString();
});

?>
