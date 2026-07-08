<?php

expect($value)->toBe(10);
expect($value)->toBeInt();
expect($value)->toBeGreaterThan(5);

-----
<?php

expect($value)->toBe(10)
    ->toBeInt()
    ->toBeGreaterThan(5);

