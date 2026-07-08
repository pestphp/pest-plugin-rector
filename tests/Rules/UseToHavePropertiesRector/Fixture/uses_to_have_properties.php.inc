<?php

namespace Tests\Rules\UseToHavePropertiesRector\Fixture;

class User {
    public string $name;
    public string $email;
}

test('uses toHaveProperties', function () {
    $user = new User();

    expect($user)->toHaveProperty('name')->toHaveProperty('email');
});

?>
-----
<?php

namespace Tests\Rules\UseToHavePropertiesRector\Fixture;

class User {
    public string $name;
    public string $email;
}

test('uses toHaveProperties', function () {
    $user = new User();

    expect($user)->toHaveProperties(['name', 'email']);
});

?>
