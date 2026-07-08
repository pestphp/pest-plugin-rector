<?php

use Illuminate\Support\Str;

// Skip non-kebab method
expect(Str::snake($value) === $value)->toBeTrue();
