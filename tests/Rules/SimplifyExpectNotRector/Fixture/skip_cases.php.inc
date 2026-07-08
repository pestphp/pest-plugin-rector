<?php

// Skip non-expect chains  
$value = !true;

// Skip non-toBeTrue/toBeFalse methods
expect(!true)->toBeString();
