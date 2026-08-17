<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(property_exists($object, 'name'))->toBeTrue();
expect(property_exists($user, 'email'))->toBeTrue();
expect(property_exists($model, 'deleted'))->toBeFalse();
