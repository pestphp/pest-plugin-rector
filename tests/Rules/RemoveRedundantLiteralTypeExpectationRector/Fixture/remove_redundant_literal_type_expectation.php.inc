<?php

#[Attribute]
final class ExampleAttribute
{
}

it('removes redundant literal type checks inside assertion chains', function (): void {
    expect('pest')
        ->toBeString()
        ->toStartWith('p');

    expect(123)
        ->not->toBeString()
        ->toBeInt();

    expect(['first', 'second'])
        ->toBeArray()
        ->toHaveCount(2);

    expect(ExampleAttribute::class)
        ->toBeString()
        ->toContain('Attribute');
});

it('keeps unsupported expectation shapes', function () use ($value): void {
    expect('pest')->toBeString();

    expect($value)
        ->toBeString()
        ->toStartWith('p');

    expect('pest')
        ->toBeInt()
        ->toStartWith('p');

    expect('pest')
        ->toBeString()
        ->and('rector')
        ->toBeString();

    expect('pest')
        // keep this explanation
        ->toBeString()
        ->toStartWith('p');

    expect('["pest"]')
        ->toBeString()
        ->json()
        ->toBeArray();

    expect(ExampleAttribute::TARGET_CLASS)
        ->toBeString()
        ->toBeInt();
});

?>
-----
<?php

#[Attribute]
final class ExampleAttribute
{
}

it('removes redundant literal type checks inside assertion chains', function (): void {
    expect('pest')
        ->toStartWith('p');

    expect(123)
        ->toBeInt();

    expect(['first', 'second'])
        ->toHaveCount(2);

    expect(ExampleAttribute::class)
        ->toContain('Attribute');
});

it('keeps unsupported expectation shapes', function () use ($value): void {
    expect('pest')->toBeString();

    expect($value)
        ->toBeString()
        ->toStartWith('p');

    expect('pest')
        ->toBeInt()
        ->toStartWith('p');

    expect('pest')
        ->toBeString()
        ->and('rector')
        ->toBeString();

    expect('pest')
        // keep this explanation
        ->toBeString()
        ->toStartWith('p');

    expect('["pest"]')
        ->toBeString()
        ->json()
        ->toBeArray();

    expect(ExampleAttribute::TARGET_CLASS)
        ->toBeString()
        ->toBeInt();
});

?>
