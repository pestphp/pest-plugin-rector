<?php

expect(abs($a - $b) < 0.001)->toBeTrue();

expect(abs($result - $expected) <= 0.1)->toBeTrue();

?>
-----
<?php

expect($a)->toEqualWithDelta($b, 0.001);

expect($result)->toEqualWithDelta($expected, 0.1);

?>
