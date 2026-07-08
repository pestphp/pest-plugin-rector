<?php

// Skip non-bool cast
expect((int) $value)->toBeTrue();

// Skip non-toBeTrue/toBeFalse
expect((bool) $value)->toBe(true);

// Skip non-expect chains
$result = (bool) $value;
