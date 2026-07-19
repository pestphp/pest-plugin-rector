<?php

test('chain expect with new method call', function () {
    expect($results->pluck('replacement')->all())
        ->toBe([
            '#fooz',
            '#Foo',
        ]);

    expect((new Hashtags())->search('doesnt_exist'))->toHaveCount(0);
});

?>
-----
<?php

test('chain expect with new method call', function () {
    expect($results->pluck('replacement')->all())
        ->toBe([
            '#fooz',
            '#Foo',
        ])
        ->and((new Hashtags())->search('doesnt_exist'))->toBeEmpty();
});

?>
