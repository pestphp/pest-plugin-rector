<?php

it('normalizes zero repeats', function (): void {
    expect(true)->toBeTrue();
})->repeat(0);

test('normalizes negative repeats', function (): void {
    expect(true)->toBeTrue();
})->group('flaky')->repeat(-2);

test('keeps valid repeats', function (): void {
    expect(true)->toBeTrue();
})->repeat(2);

it('leaves other repeat methods alone', function (): void {
    $this->builder->repeat(0);
});

?>
-----
<?php

it('normalizes zero repeats', function (): void {
    expect(true)->toBeTrue();
})->repeat(1);

test('normalizes negative repeats', function (): void {
    expect(true)->toBeTrue();
})->group('flaky')->repeat(1);

test('keeps valid repeats', function (): void {
    expect(true)->toBeTrue();
})->repeat(2);

it('leaves other repeat methods alone', function (): void {
    $this->builder->repeat(0);
});

?>
