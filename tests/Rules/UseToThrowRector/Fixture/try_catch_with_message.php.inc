<?php

test('it throws with message', function () {
    try {
        doSomething();
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('error');
    }
});

?>
-----
<?php

test('it throws with message', function () {
    expect(fn() => doSomething())->toThrow(RuntimeException::class, 'error');
});

?>
