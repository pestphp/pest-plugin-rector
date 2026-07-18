<?php

beforeAll(function (): void {
    expect(true)->toBeTrue();
});

afterAll(function (): void {
    expect(true)->toBeTrue();
});

describe('valid hooks', function (): void {
    beforeEach(function (): void {
        expect(true)->toBeTrue();
    });

    afterEach(function (): void {
        expect(true)->toBeTrue();
    });

    it('keeps expectations intact', function (): void {
        expect('pest')->toBeString();
    });
});

describe('no closure body');

describe('hook names inside tests are just values', function (): void {
    it('registers its own callbacks', function (): void {
        $hook = 'beforeAll';

        expect($hook)->toBeString();
    });
});

?>
