<?php

// Skip non-expect chains
$result = is_dir('/tmp');

// Skip non-toBeTrue methods (but toBeFalse should work with ->not modifier)
expect(is_dir('/tmp'))->toBeString();

// Skip when arg count is wrong
expect(is_dir('/tmp', 'extra'))->toBeTrue();

// Skip dynamic method names
expect($value)->{$method}(5);
