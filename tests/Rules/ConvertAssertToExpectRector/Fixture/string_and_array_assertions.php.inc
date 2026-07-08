<?php

$this->assertContains('item', $array);
$this->assertNotContains('item', $array);
$this->assertArrayHasKey('key', $array);
$this->assertArrayNotHasKey('key', $array);
$this->assertStringContainsString('needle', $haystack);
$this->assertStringNotContainsString('needle', $haystack);
$this->assertStringStartsWith('prefix', $string);
$this->assertStringEndsWith('suffix', $string);
$this->assertMatchesRegularExpression('/pattern/', $string);

?>
-----
<?php

expect($array)->toContain('item');
expect($array)->not->toContain('item');
expect($array)->toHaveKey('key');
expect($array)->not->toHaveKey('key');
expect($haystack)->toContain('needle');
expect($haystack)->not->toContain('needle');
expect($string)->toStartWith('prefix');
expect($string)->toEndWith('suffix');
expect($string)->toMatch('/pattern/');

?>
