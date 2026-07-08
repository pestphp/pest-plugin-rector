<?php

namespace Tests\Rules\UseToContainOnlyInstancesOfRector\Fixture;

class User {}

test('uses toContainOnlyInstancesOf', function () {
    $items = [new User(), new User(), new User()];

    expect($items)->each->toBeInstanceOf(User::class);
});

?>
-----
<?php

namespace Tests\Rules\UseToContainOnlyInstancesOfRector\Fixture;

class User {}

test('uses toContainOnlyInstancesOf', function () {
    $items = [new User(), new User(), new User()];

    expect($items)->toContainOnlyInstancesOf(User::class);
});

?>
