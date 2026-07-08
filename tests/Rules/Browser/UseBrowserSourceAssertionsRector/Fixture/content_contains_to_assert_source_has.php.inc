<?php

expect($page->content())->toContain('<h1>Welcome</h1>');

?>
-----
<?php

$page->assertSourceHas('<h1>Welcome</h1>');

?>
