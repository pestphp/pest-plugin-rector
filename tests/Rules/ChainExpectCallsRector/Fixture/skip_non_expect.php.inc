<?php

// Test case: skip non-expect chains - should not be transformed
$result = collect([1, 2, 3])->map(fn ($x) => $x * 2);
$other = something()->chain()->method();

