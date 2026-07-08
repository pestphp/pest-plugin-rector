<?php

expect($page->attribute('div', 'class'))->not->toContain('hidden');

?>
-----
<?php

$page->assertAttributeDoesntContain('div', 'class', 'hidden');

?>
