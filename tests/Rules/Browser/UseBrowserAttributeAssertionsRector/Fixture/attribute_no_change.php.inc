<?php

// attribute() with one arg — must not change (wrong arg count)
expect($page->attribute('div'))->toBe('something');

// attribute() with three args — must not change (unexpected signature)
expect($page->attribute('a', 'b', 'c'))->toBe('something');

// not->toBe — no supported browser assertion
expect($page->attribute('img', 'alt'))->not->toBe('wrong');

// not->toBeNull — no supported assertion
expect($page->attribute('button', 'disabled'))->not->toBeNull();

?>
