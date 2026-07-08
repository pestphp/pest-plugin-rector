<?php

expect(abs($a - $b) > 0.001)->toBeTrue();

expect(abs($a) < 0.001)->toBeTrue();

expect($a - $b < 0.001)->toBeTrue();

expect(abs($a + $b) < 0.001)->toBeTrue();

?>
