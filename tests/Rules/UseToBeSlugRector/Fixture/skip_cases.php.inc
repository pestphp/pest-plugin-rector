<?php

use Illuminate\Support\Str;

// Skip non-slug method
expect(Str::snake($value) === $value)->toBeTrue();
