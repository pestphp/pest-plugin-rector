<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(is_nan($value))->toBeTrue();
expect(is_nan($value))->toBeFalse();
