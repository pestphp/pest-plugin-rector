<?php

// not->toEqual has no browser assertion equivalent — must not change
expect($page->value('#input'))->not->toEqual('some value');

?>
