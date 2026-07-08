<?php

// Skip non-expect chains
$result = is_infinite($value);

// Skip non-toBeTrue/toBeFalse methods
expect(is_infinite($value))->toBeString();
