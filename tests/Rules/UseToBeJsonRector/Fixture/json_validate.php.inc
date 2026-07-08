<?php

// Test json_decode !== null pattern
expect(json_decode($string) !== null)->toBeTrue();
expect(json_decode($json) === null)->toBeFalse();

-----
<?php

// Test json_decode !== null pattern
expect($string)->toBeJson();
expect($json)->toBeJson();
