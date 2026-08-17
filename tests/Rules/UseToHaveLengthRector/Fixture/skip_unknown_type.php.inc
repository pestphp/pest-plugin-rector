<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(strlen($string))->toBe(10);
expect(mb_strlen($text))->toBe(5);
expect(strlen($password))->toEqual(8);
