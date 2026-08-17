<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(str_ends_with($string, 'World'))->toBeTrue();
expect(str_ends_with($filename, '.php'))->toBeTrue();
expect(str_ends_with($path, '/'))->toBeFalse();
