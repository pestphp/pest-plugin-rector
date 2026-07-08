<?php

expect($page->value('input[name=email]'))->toBe('test@example.com');

?>
-----
<?php

$page->assertValue('input[name=email]', 'test@example.com');

?>
