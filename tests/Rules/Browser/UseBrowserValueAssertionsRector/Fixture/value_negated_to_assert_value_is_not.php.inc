<?php

expect($page->value('input[name=email]'))->not->toBe('wrong@example.com');

?>
-----
<?php

$page->assertValueIsNot('input[name=email]', 'wrong@example.com');

?>
