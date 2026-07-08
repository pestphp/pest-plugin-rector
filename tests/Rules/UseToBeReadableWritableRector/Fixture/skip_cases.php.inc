<?php

// Skip non-expect chains
$result = is_readable('/tmp');
$result2 = is_writable('/tmp');

// Skip non-toBeTrue related methods
expect(is_readable('/tmp'))->toBeString();

// Skip non-readable/writable functions
expect(is_file('/tmp'))->toBeTrue();
