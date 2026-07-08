<?php

namespace Tests\Rules\UseToBeUuidRector\Fixture;

test('uses toBeUuid', function () {
    $value = '550e8400-e29b-41d4-a716-446655440000';

    expect(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value))->toBe(1);
    expect(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value))->toBeGreaterThan(0);
});

?>
-----
<?php

namespace Tests\Rules\UseToBeUuidRector\Fixture;

test('uses toBeUuid', function () {
    $value = '550e8400-e29b-41d4-a716-446655440000';

    expect($value)->toBeUuid();
    expect($value)->toBeUuid();
});

?>
