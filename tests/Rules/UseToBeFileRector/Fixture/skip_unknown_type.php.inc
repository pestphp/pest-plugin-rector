<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(is_file($path))->toBeTrue();
expect(is_file($missing))->toBeFalse();
expect(is_file($path))->toBeFalse();
