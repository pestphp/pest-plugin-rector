<?php

// content() with extra args — must not change
expect($page->content('extra'))->toContain('<h1>');

// different matcher on content() — must not change
expect($page->content())->toBe('<html>');

// not->toBe on content() — must not change
expect($page->content())->not->toBe('<html>');

?>
