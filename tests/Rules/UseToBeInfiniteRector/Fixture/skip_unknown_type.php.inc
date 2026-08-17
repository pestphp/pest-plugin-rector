<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(is_infinite($value))->toBeTrue();
expect(is_infinite($value))->toBeFalse();
