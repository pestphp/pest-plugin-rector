<?php

expect($items[0])->toBe('a');
expect($items[1])->toBe('b');
expect($items[2])->toBe('c');

?>
-----
<?php

expect($items)->sequence(fn($e) => $e->toBe('a'), fn($e) => $e->toBe('b'), fn($e) => $e->toBe('c'));
