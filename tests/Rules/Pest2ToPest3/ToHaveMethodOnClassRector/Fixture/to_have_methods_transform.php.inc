<?php

// Test toHaveMethods (plural)
expect($user)->toHaveMethods(['getName', 'getEmail']);

-----
<?php

// Test toHaveMethods (plural)
expect($user::class)->toHaveMethods(['getName', 'getEmail']);
