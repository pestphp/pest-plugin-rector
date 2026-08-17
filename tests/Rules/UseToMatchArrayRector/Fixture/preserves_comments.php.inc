<?php

test('matches the payload', function () {
    $array = ['name' => 'Nuno', 'email' => 'nuno@example.com'];

    // the user payload matters
    expect($array['name'])->toBe('Nuno');
    expect($array['email'])->toBe('nuno@example.com');
});

?>
-----
<?php

test('matches the payload', function () {
    $array = ['name' => 'Nuno', 'email' => 'nuno@example.com'];
    // the user payload matters
    expect($array)->toMatchArray(['name' => 'Nuno', 'email' => 'nuno@example.com']);
});

?>
