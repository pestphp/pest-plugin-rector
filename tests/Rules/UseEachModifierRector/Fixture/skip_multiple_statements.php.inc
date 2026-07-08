<?php

test('skips foreach with multiple statements', function () {
    $items = ['a', 'b', 'c'];

    foreach ($items as $item) {
        expect($item)->toBeString();
        expect($item)->not->toBeEmpty();
    }
});

?>
