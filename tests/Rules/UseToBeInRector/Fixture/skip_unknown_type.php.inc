<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(in_array($status, $allowedStatuses, true))->toBeTrue();
expect(in_array($value, $list, true))->toBeTrue();
