<?php

test('multiple statements in catch', function () {
    try {
        doSomething();
    } catch (RuntimeException $e) {
        log($e);
        expect($e->getMessage())->toBe('error');
    }
});

test('with finally block', function () {
    try {
        doSomething();
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('error');
    } finally {
        cleanup();
    }
});

test('multiple catches', function () {
    try {
        doSomething();
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('error');
    } catch (InvalidArgumentException $e) {
        expect($e->getMessage())->toBe('other');
    }
});

?>
