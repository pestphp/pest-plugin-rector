<?php

expect($user)->toHaveProperty('name', 'Nuno');
expect($user)->toHaveProperty('email', 'nuno@example.com');

-----
<?php

expect($user)->toMatchObject(['name' => 'Nuno', 'email' => 'nuno@example.com']);
