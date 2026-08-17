<?php

// The subject has no resolvable type, so it cannot be ruled out as an object —
// see skip_countable_object.php.inc for what the rewrite would do to one.
expect(empty($value))->toBeTrue();
expect(empty($items))->toBeFalse();
