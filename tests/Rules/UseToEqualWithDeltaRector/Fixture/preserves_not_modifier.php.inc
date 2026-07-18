<?php

expect(abs($a - $b) < 0.001)->not->toBeTrue();

?>
-----
<?php

expect($a)->not->toEqualWithDelta($b, 0.001);

?>
