<?php

// Skip without sort on both sides
expect(sort($a))->toEqual($b);
expect($a)->toEqual(sort($b));

// Skip non-toEqual/toBe matchers
expect(sort($a))->toBeArray();
