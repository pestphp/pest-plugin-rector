<?php

expect(sort($a))->toEqual(sort($b));
expect(sort($a))->toBe(sort($b));

?>
-----
<?php

expect($a)->toEqualCanonicalizing($b);
expect($a)->toEqualCanonicalizing($b);

?>
