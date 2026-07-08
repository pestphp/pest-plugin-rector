<?php

// Test case: already correct order - no changes needed
expect($a)->toBeInt()->toBe(10);
expect($b)->toBeString()->toContain('test');

?>
-----
<?php

// Test case: already correct order - no changes needed
expect($a)->toBeInt()->toBe(10);
expect($b)->toBeString()->toContain('test');

?>
