<?php

$this->assertIsList($values);
$this->assertIsNotArray($value);
$this->assertIsNotBool($value);
$this->assertIsNotFloat($value);
$this->assertIsNotInt($value);
$this->assertIsNotString($value);
$this->assertIsNotNumeric($value);
$this->assertIsNotObject($value);
$this->assertIsNotCallable($value);
$this->assertIsNotIterable($value);
$this->assertIsNotScalar($value);
$this->assertIsNotResource($value);
$this->assertContainsOnlyInstancesOf(User::class, $users);
$this->assertSameSize($expected, $actual);
$this->assertObjectHasProperty('name', $user);
$this->assertObjectNotHasProperty('password', $user);
$this->assertEqualsCanonicalizing(['b', 'a'], $letters);
$this->assertEqualsWithDelta(10.5, $score, 0.1);

?>
-----
<?php

expect($values)->toBeList();
expect($value)->not->toBeArray();
expect($value)->not->toBeBool();
expect($value)->not->toBeFloat();
expect($value)->not->toBeInt();
expect($value)->not->toBeString();
expect($value)->not->toBeNumeric();
expect($value)->not->toBeObject();
expect($value)->not->toBeCallable();
expect($value)->not->toBeIterable();
expect($value)->not->toBeScalar();
expect($value)->not->toBeResource();
expect($users)->toContainOnlyInstancesOf(User::class);
expect($actual)->toHaveSameSize($expected);
expect($user)->toHaveProperty('name');
expect($user)->not->toHaveProperty('password');
expect($letters)->toEqualCanonicalizing(['b', 'a']);
expect($score)->toEqualWithDelta(10.5, 0.1);

?>
