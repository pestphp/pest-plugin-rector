<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(in_array($item, $array))->toBeTrue();
expect(in_array($needle, $haystack, true))->toBeTrue();
expect(in_array('value', $list))->toBeTrue();
expect(in_array($item, $array))->toBeFalse();
