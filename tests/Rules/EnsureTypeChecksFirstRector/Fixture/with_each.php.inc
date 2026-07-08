<?php

// Test case for each() modifier - should preserve scoping semantics
// The each() modifier should not be reordered
expect([1, 2, 3])->each()->toBeInt();

?>
-----
<?php

// Test case for each() modifier - should preserve scoping semantics
// The each() modifier should not be reordered
expect([1, 2, 3])->each()->toBeInt();

?>
