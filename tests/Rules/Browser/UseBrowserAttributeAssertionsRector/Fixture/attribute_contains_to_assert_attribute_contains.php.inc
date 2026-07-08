<?php

expect($page->attribute('div', 'class'))->toContain('container');

?>
-----
<?php

$page->assertAttributeContains('div', 'class', 'container');

?>
