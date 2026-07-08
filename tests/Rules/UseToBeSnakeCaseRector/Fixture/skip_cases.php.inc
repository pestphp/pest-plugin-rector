<?php

use Illuminate\Support\Str;

// Skip non-Str class
expect(OtherClass::snake($value) === $value)->toBeTrue();

// Skip non-snake method
expect(Str::camel($value) === $value)->toBeTrue();

// Skip non-toBeTrue
expect(Str::snake($value) === $value)->toBeFalse();
