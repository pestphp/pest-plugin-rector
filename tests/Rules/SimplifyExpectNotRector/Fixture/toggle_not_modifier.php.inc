<?php

expect(!$array)->toBeEmpty();
expect(!$array)->not->toBeEmpty();

?>
-----
<?php

expect($array)->not->toBeEmpty();
expect($array)->toBeEmpty();

?>
