<?php

test('reorders separate expects for same subject', function () {
    $a = 10;

    expect($a)->toBe(10);
    expect($a)->toBeInt();
});

-----
<?php

test('reorders separate expects for same subject', function () {
    $a = 10;
    expect($a)->toBeInt();
    expect($a)->toBe(10);
});
