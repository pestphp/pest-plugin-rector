<?php

expect("{$first} {$last}")->toBeString();
expect("{$first} {$last}")->toContain(' ');

?>

-----
<?php

expect("{$first} {$last}")->toBeString()
    ->toContain(' ');

?>
