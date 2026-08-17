<?php

/**
 * @var object $user
 */
expect($user)->toHaveProperty('name', 'Nuno');
expect($user)->toHaveProperty('email', 'nuno@example.com');

-----
<?php

/**
 * @var object $user
 */
expect($user)->toMatchObject(['name' => 'Nuno', 'email' => 'nuno@example.com']);
