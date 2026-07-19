<?php

expect($results->pluck('replacement')->all())
    ->toBe([
        '#fooz',
        '#Foo',
    ]);

expect($other)->toHaveCount(0);

?>
-----
<?php

expect($results->pluck('replacement')->all())
    ->toBe([
        '#fooz',
        '#Foo',
    ])
    ->and($other)->toBeEmpty();

?>
