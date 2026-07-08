<?php

use Illuminate\Support\Str;

// Skip non-camel method
expect(Str::snake($value) === $value)->toBeTrue();
