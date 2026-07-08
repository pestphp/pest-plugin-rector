<?php

expect(in_array($item, $array))->toBeTrue();
expect(in_array($item, $array, true))->toBeTrue();
expect(in_array($item, $array, $strict))->toBeTrue();

?>
