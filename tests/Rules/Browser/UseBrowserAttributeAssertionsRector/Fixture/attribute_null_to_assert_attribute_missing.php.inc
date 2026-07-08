<?php

expect($page->attribute('button', 'disabled'))->toBeNull();

?>
-----
<?php

$page->assertAttributeMissing('button', 'disabled');

?>
