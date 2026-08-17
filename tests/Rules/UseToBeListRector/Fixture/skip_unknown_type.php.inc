<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(array_is_list($array))->toBeTrue();
expect(array_is_list($array))->toBeFalse();
