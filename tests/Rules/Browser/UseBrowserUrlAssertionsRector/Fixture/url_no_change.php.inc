<?php

// url() with extra args — must not change (unexpected signature)
expect($page->url('extra'))->toBe('https://example.com');

// non-url method — must not change
expect($page->content())->toBe('<html>');

// different matcher on url() — must not change
expect($page->url())->toContain('/home');

?>
