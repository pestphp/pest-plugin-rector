<?php

// --- Skip: consecutive expects with ->not modifier on same variable ---
expect($value)->not->toBe(5);
expect($value)->toBeInt();

// --- Skip: expects inside test closure with comments ---
test('sample', function () {
    // Consecutive expects with comments should not be merged
    expect($value)->toBe(1); // keep separate
    expect($value)->toBeInt();
});

// --- Skip: non-expression statements between expects ---
$value = 10;
expect($value)->toBe(10);

throw new Exception('test');
expect($value)->toBeInt();

// --- Skip: ->not modifier in chain ---
expect($value)->not->toBe(5);
expect($value)->not->toBeInt()->not->toBeNull();
