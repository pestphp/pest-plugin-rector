<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(in_array($item, $array, false))->toBeTrue();
expect(in_array($item, $array, false))->toBeFalse();
expect(in_array('1', $numbers, false))->toBeTrue();
