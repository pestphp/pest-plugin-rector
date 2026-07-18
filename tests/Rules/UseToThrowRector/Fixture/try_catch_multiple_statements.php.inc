<?php

test('it throws after multiple statements', function () {
    try {
        $user = createUser();
        $user->activate();
        doSomething($user);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('error');
    }
});

test('it captures outer variables', function () {
    $service = makeService();

    try {
        $service->boot();
        $service->run();
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('boom');
    }
});

?>
-----
<?php

test('it throws after multiple statements', function () {
    expect(function () {
        $user = createUser();
        $user->activate();
        doSomething($user);
    })->toThrow(RuntimeException::class, 'error');
});

test('it captures outer variables', function () {
    $service = makeService();

    expect(function () use ($service) {
        $service->boot();
        $service->run();
    })->toThrow(RuntimeException::class, 'boom');
});

?>
