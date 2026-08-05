<?php

expect("{$guard->shouldDispatch()}")->toBe('1');
expect("{$guard->shouldDispatch()}")->toBe('');

?>

-----
<?php

expect("{$guard->shouldDispatch()}")->toBe('1')
    ->and("{$guard->shouldDispatch()}")->toBe('');

?>
