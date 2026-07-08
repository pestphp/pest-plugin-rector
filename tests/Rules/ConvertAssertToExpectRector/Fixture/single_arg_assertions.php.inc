<?php

$this->assertTrue($value);
$this->assertFalse($result);
$this->assertNull($user);
$this->assertNotNull($user);
$this->assertEmpty($items);
$this->assertNotEmpty($items);

?>
-----
<?php

expect($value)->toBeTrue();
expect($result)->toBeFalse();
expect($user)->toBeNull();
expect($user)->not->toBeNull();
expect($items)->toBeEmpty();
expect($items)->not->toBeEmpty();

?>
