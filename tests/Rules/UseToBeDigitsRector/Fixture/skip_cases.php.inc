<?php

// Skip non-expect chains
$result = ctype_digit($value);

// Skip non-toBeTrue/toBeFalse methods
expect(ctype_digit($value))->toBeString();

// Skip when arg count is wrong
expect(ctype_digit($value, 'extra'))->toBeTrue();
