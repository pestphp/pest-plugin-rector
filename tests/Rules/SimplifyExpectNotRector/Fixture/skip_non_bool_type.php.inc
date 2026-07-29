<?php

$haystack = (string) random_int(0, 9);

expect(!random_int(0, 9))->toBeTrue();
expect(!mb_strstr($haystack, 'needle'))->toBeTrue();

?>
