<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(count($array))->toBe(5);
expect(sizeof($items))->toEqual(3);
