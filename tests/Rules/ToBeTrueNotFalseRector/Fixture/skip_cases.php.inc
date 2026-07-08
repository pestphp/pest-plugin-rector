<?php

// Skip non-expect chains
$value = false;

// Skip non-toBeTrue methods for toBeFalse match
expect(false)->toBeString();

// Skip when not is on other place in chain
expect($user->pqr)->not->toBeNull()
    ->and($user->abc)->not->toBeNull()
    ->and($user->is_done)->toBeFalse()
    ->and($user->xyz)->toBeSomething();
