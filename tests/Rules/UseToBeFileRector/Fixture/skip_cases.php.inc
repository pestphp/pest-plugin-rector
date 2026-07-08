<?php

// Skip non-expect chains
$result = is_file('/tmp/file.txt');

// Skip non-toBeTrue methods (but toBeFalse works with ->not)
expect(is_file('/tmp/file.txt'))->toBeString();

// Skip when arg count is wrong
expect(is_file('/tmp/file.txt', 'extra'))->toBeTrue();
