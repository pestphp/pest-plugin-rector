<?php

expect($page->script('document.querySelector(".btn").disabled'))->toEqual(true);

?>
-----
<?php

$page->assertScript('document.querySelector(".btn").disabled', true);

?>
