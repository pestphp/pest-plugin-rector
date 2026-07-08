<?php

expect($page->content())->not->toContain('<div class="error">');

?>
-----
<?php

$page->assertSourceMissing('<div class="error">');

?>
