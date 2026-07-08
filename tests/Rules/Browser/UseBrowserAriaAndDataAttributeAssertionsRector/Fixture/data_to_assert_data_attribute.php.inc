<?php

expect($page->attribute('div', 'data-id'))->toBe('123');

?>
-----
<?php

$page->assertDataAttribute('div', 'id', '123');

?>
