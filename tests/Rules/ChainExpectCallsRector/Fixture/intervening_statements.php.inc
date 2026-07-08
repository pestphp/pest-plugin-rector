<?php

$a = 10;

expect($a)->toBe(10);

$x = 5; // some other statement in between

expect($a)->toBeInt();

// Verify first post has both creator and updater
expect($models[0]->creator->name)->toBe('John Creator')
    ->and($models[0]->updater->name)->toBe('Jane Updater');

// Verify second post has only creator
expect($models[1]->creator->name)->toBe('Bob Viewer')
    ->and(isset($models[1]->updater))->toBeFalse();

// Verify third post has both creator and updater (different combination)
expect($models[2]->creator->name)->toBe('John Creator')
    ->and($models[2]->updater->name)->toBe('Bob Viewer');
