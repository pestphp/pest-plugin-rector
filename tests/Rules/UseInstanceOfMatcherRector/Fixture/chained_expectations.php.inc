<?php

namespace Tests\Rules\UseInstanceOfMatcherRector\Fixture;

use DateTimeInterface;

test('simplifies instanceof inside and chains', function () {
    $user = makeUser();

    expect($user->id)->toBeInt()
        ->and($user->created_at instanceof DateTimeInterface)->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseInstanceOfMatcherRector\Fixture;

use DateTimeInterface;

test('simplifies instanceof inside and chains', function () {
    $user = makeUser();

    expect($user->id)->toBeInt()
        ->and($user->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

?>
