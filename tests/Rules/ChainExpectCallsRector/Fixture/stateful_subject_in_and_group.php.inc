<?php

expect($a)->toBe(1);
expect($guard->shouldDispatch())->toBeTrue();
expect($guard->shouldDispatch())->toBeFalse();

?>

-----
<?php

expect($a)->toBe(1)
    ->and($guard->shouldDispatch())->toBeTrue()
    ->and($guard->shouldDispatch())->toBeFalse();

?>
