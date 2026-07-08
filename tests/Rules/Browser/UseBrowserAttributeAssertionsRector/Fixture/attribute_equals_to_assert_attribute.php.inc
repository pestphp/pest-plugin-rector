<?php

expect($page->attribute('img', 'alt'))->toBe('Profile Picture');

?>
-----
<?php

$page->assertAttribute('img', 'alt', 'Profile Picture');

?>
