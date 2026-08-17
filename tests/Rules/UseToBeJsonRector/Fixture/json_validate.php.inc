<?php

/**
 * @var string $json
 * @var string $string
 */
// Test json_decode !== null pattern
expect(json_decode($string) !== null)->toBeTrue();
expect(json_decode($json) === null)->toBeFalse();

-----
<?php

/**
 * @var string $json
 * @var string $string
 */
// Test json_decode !== null pattern
expect($string)->toBeJson();
expect($json)->toBeJson();
