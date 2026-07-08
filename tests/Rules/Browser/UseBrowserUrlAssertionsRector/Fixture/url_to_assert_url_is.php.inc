<?php

expect($page->url())->toBe('https://example.com/home');

?>
-----
<?php

$page->assertUrlIs('https://example.com/home');

?>
