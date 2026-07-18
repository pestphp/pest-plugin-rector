<?php

test('keeps the comment', function () {
    expect(true)->toBeTrue();
})->repeat(/* why zero? */ 0);

?>
-----
<?php

test('keeps the comment', function () {
    expect(true)->toBeTrue();
})->repeat(/* why zero? */ 1);

?>
