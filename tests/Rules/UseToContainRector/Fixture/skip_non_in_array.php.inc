<?php

// Should not transform - not in_array
expect($value)->toBeTrue();
expect(someFunction($item, $array))->toBeTrue();
expect(str_contains($string, 'needle'))->toBeTrue();

// Should not transform - already using toContain
expect($array)->toContain($item);

?>
