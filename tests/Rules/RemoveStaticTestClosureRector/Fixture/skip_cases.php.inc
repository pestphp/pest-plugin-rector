<?php

test('static closure without test case usage', static function (): void {
    expect(true)->toBeTrue();
});

it('static arrow function without test case usage', static fn () => expect(1)->toBe(1));

beforeEach(static function (): void {
    expect(getenv('APP_ENV'))->not->toBeFalse();
});

beforeAll(static function (): void {
    expect(getenv('APP_ENV'))->not->toBeFalse();
});

afterAll(static function (): void {
    expect(getenv('APP_ENV'))->not->toBeFalse();
});

test('already non static closure', function (): void {
    expect($this)->not->toBeNull();
});

runner('non pest function', static function (): void {
    $this->cleanup();
});

test('this only inside nested static closure', static function (): void {
    $callback = static function (): string {
        return $this->app->getLocale();
    };

    expect($callback)->toBeCallable();
});

?>
