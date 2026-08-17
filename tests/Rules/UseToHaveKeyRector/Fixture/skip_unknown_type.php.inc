<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(array_key_exists('id', $array))->toBeTrue();
expect(array_key_exists($key, $data))->toBeTrue();
expect(array_key_exists('name', $user))->toBeFalse();
