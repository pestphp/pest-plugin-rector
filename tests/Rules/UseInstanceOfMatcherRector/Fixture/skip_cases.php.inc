<?php

// Skip non-expect chains
$result = $user instanceof User;

// Skip non-toBeTrue methods
expect($user instanceof User)->toBeFalse();

// Skip non-instanceof expressions
expect($user)->toBeTrue();

// Skip when class is dynamic
expect($user instanceof $className)->toBeTrue();
