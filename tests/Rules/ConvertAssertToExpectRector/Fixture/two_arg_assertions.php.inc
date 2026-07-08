<?php

$this->assertEquals('expected', $result);
$this->assertNotEquals('expected', $result);
$this->assertSame(42, $value);
$this->assertNotSame(42, $value);
$this->assertCount(3, $items);
$this->assertInstanceOf(User::class, $result);
$this->assertNotInstanceOf(User::class, $result);

?>
-----
<?php

expect($result)->toEqual('expected');
expect($result)->not->toEqual('expected');
expect($value)->toBe(42);
expect($value)->not->toBe(42);
expect($items)->toHaveCount(3);
expect($result)->toBeInstanceOf(User::class);
expect($result)->not->toBeInstanceOf(User::class);

?>
