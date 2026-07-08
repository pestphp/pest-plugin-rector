<?php

expect($a === $b)->toBeTrue();
expect($value === 'expected')->toBeTrue();
expect($a !== $b)->toBeTrue();
expect($x === $y)->toBeFalse();

?>
-----
<?php

expect($a)->toBe($b);
expect($value)->toBe('expected');
expect($a)->not->toBe($b);
expect($x)->not->toBe($y);

?>
