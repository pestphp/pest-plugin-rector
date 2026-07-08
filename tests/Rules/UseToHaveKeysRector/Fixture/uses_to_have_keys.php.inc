<?php

namespace Tests\Rules\UseToHaveKeysRector\Fixture;

test('uses toHaveKeys', function () {
    $array = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

    expect($array)->toHaveKey('id')->toHaveKey('name')->toHaveKey('email');
    expect($array)->toHaveKey('foo')->toHaveKey('bar');
});

?>
-----
<?php

namespace Tests\Rules\UseToHaveKeysRector\Fixture;

test('uses toHaveKeys', function () {
    $array = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];

    expect($array)->toHaveKeys(['id', 'name', 'email']);
    expect($array)->toHaveKeys(['foo', 'bar']);
});

?>
