<?php

// Skip non-expect chains  
$value = (true === true);

// Skip non-toBeTrue/toBeFalse methods
expect(true)->toBeString();
