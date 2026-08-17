<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(ctype_alnum($value))->toBeFalse();
expect(ctype_alnum($value))->not->toBeTrue();
