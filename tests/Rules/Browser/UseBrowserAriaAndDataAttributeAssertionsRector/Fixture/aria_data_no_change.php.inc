<?php

// regular attribute — not aria-* or data-*, no change
expect($page->attribute('img', 'alt'))->toBe('Profile Picture');

// aria- with toContain — no dedicated method, no change
expect($page->attribute('div', 'aria-label'))->toContain('text');

// not->toBe — not handled, no change
expect($page->attribute('button', 'aria-expanded'))->not->toBe('true');

// dynamic attribute name — not a string literal, no change
expect($page->attribute('button', $attrName))->toBe('value');

?>
