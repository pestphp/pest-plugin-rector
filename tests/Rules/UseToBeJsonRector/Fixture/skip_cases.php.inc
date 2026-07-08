<?php

// Skip non-expect chains
$result = json_decode('{}');

// Skip non-toBeTrue methods
expect(json_validate('{}'))->toBeFalse();

// Skip for non-json functions
expect($value)->toBeTrue();
