<?php

namespace Tests\Rules\UseToBeUrlRector\Fixture;

test('uses toBeUrl', function () {
    $url = 'https://example.com';

    expect(filter_var($url, FILTER_VALIDATE_URL))->not->toBeFalse();
    expect(filter_var($url, FILTER_VALIDATE_URL) !== false)->toBeTrue();
});

?>
-----
<?php

namespace Tests\Rules\UseToBeUrlRector\Fixture;

test('uses toBeUrl', function () {
    $url = 'https://example.com';

    expect($url)->toBeUrl();
    expect($url)->toBeUrl();
});

?>
