<?php

expect($results->pluck('replacement')->all())
    ->toBe([
        '#fooz',
        '#Foo',
    ]);

expect((new Hashtags())->search('doesnt_exist'))->toHaveCount(0);

?>
-----
<?php

expect($results->pluck('replacement')->all())
    ->toBe([
        '#fooz',
        '#Foo',
    ])
    ->and((new Hashtags())->search('doesnt_exist'))->toBeEmpty();

?>
