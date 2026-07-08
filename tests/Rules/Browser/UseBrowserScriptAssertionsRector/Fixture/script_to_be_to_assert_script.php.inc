<?php

expect($page->script('document.title'))->toBe('Home Page');

?>
-----
<?php

$page->assertScript('document.title', 'Home Page');

?>
