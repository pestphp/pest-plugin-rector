<?php

// The subjects below have no resolvable type, so the rewrite is not proven
// safe and the rule must leave the code alone.
expect(is_readable($path))->toBeTrue();
expect(is_writable($file))->toBeTrue();
expect(is_writeable($dir))->toBeTrue();
expect(is_readable($locked))->toBeFalse();
expect(is_readable($path))->toBeFalse();
expect(is_writable($path))->toBeFalse();
expect(is_writeable($path))->toBeTrue();
