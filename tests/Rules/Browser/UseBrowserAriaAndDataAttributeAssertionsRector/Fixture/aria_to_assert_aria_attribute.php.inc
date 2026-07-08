<?php

expect($page->attribute('button', 'aria-label'))->toBe('Close');

?>
-----
<?php

$page->assertAriaAttribute('button', 'label', 'Close');

?>
