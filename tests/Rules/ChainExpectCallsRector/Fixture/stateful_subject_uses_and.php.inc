<?php

expect($guard->shouldDispatch())->toBeTrue();
expect($guard->shouldDispatch())->toBeFalse();

?>

-----
<?php

expect($guard->shouldDispatch())->toBeTrue()
    ->and($guard->shouldDispatch())->toBeFalse();

?>
