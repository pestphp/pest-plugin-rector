<?php

test('it throws', function () {
    try {
        riskyOperation();
    } catch (\InvalidArgumentException $e) {
    }
});

?>
-----
<?php

test('it throws', function () {
    expect(fn() => riskyOperation())->toThrow(\InvalidArgumentException::class);
});

?>
