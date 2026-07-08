<?php

// negated: not->toBe on url() — no supported browser assertion, must not change
expect($page->url())->not->toBe('https://example.com/login');

?>
