<?php

namespace Tests\Rules\UseTypeMatchersRector\Fixture;

test('simplifies type checks inside and chains', function () {
    $name = 'Nuno';
    $roles = ['owner'];

    expect($name)->toBe('Nuno')
        ->and(is_array($roles))->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseTypeMatchersRector\Fixture;

test('simplifies type checks inside and chains', function () {
    $name = 'Nuno';
    $roles = ['owner'];

    expect($name)->toBe('Nuno')
        ->and($roles)->toBeArray();
});

?>
