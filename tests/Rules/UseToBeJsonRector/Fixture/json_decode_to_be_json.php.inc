<?php

/**
 * @var string $json
 * @var string $response
 * @var string $string
 */
expect(json_decode($string) !== null)->toBeTrue();
expect(json_decode($json) === null)->toBeFalse();
expect(json_decode($response) !== null)->toBeFalse();

?>
-----
<?php

/**
 * @var string $json
 * @var string $response
 * @var string $string
 */
expect($string)->toBeJson();
expect($json)->toBeJson();
expect($response)->not->toBeJson();

?>
