<?php

expect(property_exists($object, 'name'))->toBeTrue();
expect(property_exists($user, 'email'))->toBeTrue();
expect(property_exists($model, 'deleted'))->toBeFalse();

?>
-----
<?php

expect($object)->toHaveProperty('name');
expect($user)->toHaveProperty('email');
expect($model)->not->toHaveProperty('deleted');

?>
