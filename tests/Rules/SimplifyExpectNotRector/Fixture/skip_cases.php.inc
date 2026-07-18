<?php

// Skip non-expect chains  
$value = !true;

// Skip non-toBeTrue/toBeFalse methods
expect(!true)->toBeString();

// Skip when other matchers in the chain assert on the negated subject
expect(!$value)->toBeString()->toBeTrue();
