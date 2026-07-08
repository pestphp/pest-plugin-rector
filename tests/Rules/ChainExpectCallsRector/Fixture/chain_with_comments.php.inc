<?php

expect($value)->toBeInt();
expect($value)->toBe(10);
// some comment
expect($value)->toBeGreaterThan(5);

// comment 1
expect($valueB)->toBeInt();
// comment 2
expect($valueB)->toBe(10);

-----
<?php

expect($value)->toBeInt()
    ->toBe(10);
// some comment
expect($value)->toBeGreaterThan(5);

// comment 1
expect($valueB)->toBeInt();
// comment 2
expect($valueB)->toBe(10);
