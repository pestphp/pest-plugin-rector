<?php

use Illuminate\Support\Str;

// Skip non-studly method
expect(Str::snake($value) === $value)->toBeTrue();
