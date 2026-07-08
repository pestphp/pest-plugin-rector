<?php

beforeAll(function (): void {
    expect(true)->toBeTrue();
});

describe('users', function (): void {
    beforeAll(function (): void {
        expect($this->app)->not->toBeNull();
    });

    afterAll(function (): void {
        expect(['cleanup'])
            ->each(fn ($value) => $value->toBeString());
    });

    beforeEach(function (): void {
        expect('already valid')->toBeString();
    });

    it('keeps expectations intact', function (): void {
        expect('pest')
            ->toBeString()
            ->and('rector')
            ->toBeString();
    });

    describe('nested team hooks', function (): void {
        beforeAll(function (): void {
            expect(2)->toBeGreaterThan(1);
        });

        afterAll(function (): void {
            expect(['nested'])->toContain('nested');
        });
    });
});

?>
-----
<?php

beforeAll(function (): void {
    expect(true)->toBeTrue();
});

describe('users', function (): void {
    beforeEach(function (): void {
        expect($this->app)->not->toBeNull();
    });

    afterEach(function (): void {
        expect(['cleanup'])
            ->each(fn ($value) => $value->toBeString());
    });

    beforeEach(function (): void {
        expect('already valid')->toBeString();
    });

    it('keeps expectations intact', function (): void {
        expect('pest')
            ->toBeString()
            ->and('rector')
            ->toBeString();
    });

    describe('nested team hooks', function (): void {
        beforeEach(function (): void {
            expect(2)->toBeGreaterThan(1);
        });

        afterEach(function (): void {
            expect(['nested'])->toContain('nested');
        });
    });
});

?>