<?php

/**
 * @var array $array
 * @var array $items
 */
// Test sizeof function (alias for count)
expect(sizeof($array))->toBe(5);
expect(sizeof($items))->toEqual(3);

-----
<?php

/**
 * @var array $array
 * @var array $items
 */
// Test sizeof function (alias for count)
expect($array)->toHaveCount(5);
expect($items)->toHaveCount(3);
