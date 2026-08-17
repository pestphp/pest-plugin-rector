<?php

/**
 * @var string $json
 * @var string $response
 * @var string $string
 */
expect(json_decode($string) !== null)->not->toBeTrue();
expect(json_decode($json) === null)->not->toBeFalse();
expect(json_decode($response) !== null)->not->toBeFalse();

?>
-----
<?php

/**
 * @var string $json
 * @var string $response
 * @var string $string
 */
expect($string)->not->toBeJson();
expect($json)->not->toBeJson();
expect($response)->toBeJson();

?>
