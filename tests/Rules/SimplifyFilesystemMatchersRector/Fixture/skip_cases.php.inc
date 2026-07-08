<?php

// Skip: different paths
expect(is_file($path1) && is_readable($path2))->toBeTrue();

// Skip: non-matching function pairs
expect(is_file($path) && is_string($path))->toBeTrue();

// Skip: not toBeTrue
expect(is_file($path) && is_readable($path))->toBeFalse();

// Skip: chained with arguments
expect($path)->toBeFile()->toBeReadable('custom message');

// Skip: single function
expect(is_file($path))->toBeTrue();
