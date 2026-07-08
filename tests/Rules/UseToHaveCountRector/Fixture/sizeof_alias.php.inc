<?php

// Test sizeof function (alias for count)
expect(sizeof($array))->toBe(5);
expect(sizeof($items))->toEqual(3);

-----
<?php

// Test sizeof function (alias for count)
expect($array)->toHaveCount(5);
expect($items)->toHaveCount(3);
