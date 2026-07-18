<?php

// `!$value` is always a boolean and never null, so these must not be rewritten
// to `expect($value)->not->toBeNull()` — that would flip a failing test to passing.

expect(!$value)->toBeNull();
expect(!$value)->not->toBeNull();
expect(!$this->resolve())->toBeNull();
expect(!($a && $b))->toBeNull();

?>
