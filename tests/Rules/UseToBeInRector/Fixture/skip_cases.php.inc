<?php

// Skip non-expect chains
$result = in_array($value, ['pending', 'active']);

// Skip non-toBeTrue/toBeFalse methods
expect(in_array($value, ['a']))->toBeString();

// Skip when arg count is wrong (only 1 arg)
expect(in_array($value))->toBeTrue();
