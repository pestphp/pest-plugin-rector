<?php

// The path has no resolvable type, so `expect($path)` is not proven to be the
// filesystem subject the combined matcher asserts against. Leave it alone.
expect(is_file($path) && is_readable($path))->toBeTrue();
expect(is_dir($path) && is_writable($path))->toBeTrue();
