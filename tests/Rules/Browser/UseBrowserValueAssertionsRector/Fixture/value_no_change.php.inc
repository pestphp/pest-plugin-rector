<?php

// value() with no selector — must not change (wrong arg count)
expect($page->value())->toBe('something');

// value() with two selectors — must not change (unexpected signature)
expect($page->value('a', 'b'))->toBe('something');

// different matcher on value() — must not change
expect($page->value('#input'))->toContain('text');

?>
