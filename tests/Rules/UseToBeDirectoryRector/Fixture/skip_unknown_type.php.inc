<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(is_dir($path))->toBeTrue();
expect(is_dir($missing))->toBeFalse();
expect(is_dir($path))->toBeFalse();
