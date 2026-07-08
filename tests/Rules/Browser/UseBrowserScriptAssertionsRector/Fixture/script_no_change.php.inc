<?php

// not->toBe on script() — must not change
expect($page->script('document.title'))->not->toBe('Wrong Title');

// script() with zero args — must not change
expect($page->script())->toBe('something');

// script() with two args — must not change (unexpected signature)
expect($page->script('a', 'b'))->toBe('something');

// different matcher on script() — must not change
expect($page->script('document.title'))->toContain('Page');

?>
