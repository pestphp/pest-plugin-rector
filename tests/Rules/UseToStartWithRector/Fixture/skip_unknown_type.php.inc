<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(str_starts_with($string, 'Hello'))->toBeTrue();
expect(str_starts_with($text, $prefix))->toBeTrue();
expect(str_starts_with($url, 'https://'))->toBeFalse();
