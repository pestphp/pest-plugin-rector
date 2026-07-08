<?php

// Skip non-expect chains  
$value = ($a === $b);

// Skip non-toBeTrue methods
expect($a === $b)->toBeString();
