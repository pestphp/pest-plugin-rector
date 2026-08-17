<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(json_decode($string) !== null)->toBeTrue();
expect(json_decode($json) === null)->toBeFalse();
expect(json_decode($response) !== null)->toBeFalse();
expect(json_decode($string) !== null)->not->toBeTrue();
expect(json_decode($json) === null)->not->toBeFalse();
expect(json_decode($response) !== null)->not->toBeFalse();
