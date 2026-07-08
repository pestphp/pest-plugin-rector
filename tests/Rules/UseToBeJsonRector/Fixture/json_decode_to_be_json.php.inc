<?php

expect(json_decode($string) !== null)->toBeTrue();
expect(json_decode($json) === null)->toBeFalse();
expect(json_decode($response) !== null)->toBeFalse();

?>
-----
<?php

expect($string)->toBeJson();
expect($json)->toBeJson();
expect($response)->not->toBeJson();

?>
