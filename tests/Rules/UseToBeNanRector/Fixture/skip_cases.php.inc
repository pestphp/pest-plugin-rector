<?php

// Skip non-expect chains
$result = is_nan($value);

// Skip non-toBeTrue/toBeFalse methods
expect(is_nan($value))->toBeString();
