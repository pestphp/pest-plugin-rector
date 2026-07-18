<?php

namespace Tests\Rules\UseToContainRector\Fixture;

test('simplifies in_array inside and chains', function () {
    $roles = ['owner', 'admin', 'member'];

    expect($roles)->toBeArray()
        ->and(in_array('admin', $roles))->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseToContainRector\Fixture;

test('simplifies in_array inside and chains', function () {
    $roles = ['owner', 'admin', 'member'];

    expect($roles)->toBeArray()
        ->and($roles)->toContain('admin');
});

?>
