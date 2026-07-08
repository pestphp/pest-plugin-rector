<?php

namespace Tests\Rules\UseToMatchArrayRector\Fixture;

test('uses toMatchArray', function () {
    $array = ['name' => 'Nuno', 'email' => 'nuno@example.com'];

    expect($array['name'])->toBe('Nuno');
    expect($array['email'])->toBe('nuno@example.com');
});

?>
-----
<?php

namespace Tests\Rules\UseToMatchArrayRector\Fixture;

test('uses toMatchArray', function () {
    $array = ['name' => 'Nuno', 'email' => 'nuno@example.com'];
    expect($array)->toMatchArray(['name' => 'Nuno', 'email' => 'nuno@example.com']);
});

?>
