<?php

// Skip non-expect chains
$result = array_is_list($array);

// Skip non-toBeTrue/toBeFalse methods
expect(array_is_list($array))->toBeString();

// Skip when arg count is wrong
expect(array_is_list($array, 'extra'))->toBeTrue();
