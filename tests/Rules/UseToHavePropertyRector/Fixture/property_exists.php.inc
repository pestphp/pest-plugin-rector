<?php

/**
 * @var object $model
 * @var object $object
 * @var object $user
 */
expect(property_exists($object, 'name'))->toBeTrue();
expect(property_exists($user, 'email'))->toBeTrue();
expect(property_exists($model, 'deleted'))->toBeFalse();

?>
-----
<?php

/**
 * @var object $model
 * @var object $object
 * @var object $user
 */
expect($object)->toHaveProperty('name');
expect($user)->toHaveProperty('email');
expect($model)->not->toHaveProperty('deleted');

?>
