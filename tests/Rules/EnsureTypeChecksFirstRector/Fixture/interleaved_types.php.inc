<?php

// Test case: interleaved type and non-type matchers
// Pattern: type -> non-type -> type - should NOT be reordered
expect($a)->toBeInt()->toBe(5)->toBeFloat();

?>
-----
<?php

// Test case: interleaved type and non-type matchers
// Pattern: type -> non-type -> type - should NOT be reordered
expect($a)->toBeInt()->toBe(5)->toBeFloat();

?>
