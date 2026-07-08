<?php

$this->assertIsArray($value);
$this->assertIsBool($value);
$this->assertIsFloat($value);
$this->assertIsInt($value);
$this->assertIsString($value);
$this->assertIsNumeric($value);
$this->assertIsObject($value);
$this->assertIsCallable($value);
$this->assertFileExists($path);
$this->assertDirectoryExists($path);
$this->assertJson($string);
$this->assertGreaterThan(10, $value);
$this->assertGreaterThanOrEqual(10, $value);
$this->assertLessThan(10, $value);
$this->assertLessThanOrEqual(10, $value);

?>
-----
<?php

expect($value)->toBeArray();
expect($value)->toBeBool();
expect($value)->toBeFloat();
expect($value)->toBeInt();
expect($value)->toBeString();
expect($value)->toBeNumeric();
expect($value)->toBeObject();
expect($value)->toBeCallable();
expect($path)->toBeFile();
expect($path)->toBeDirectory();
expect($string)->toBeJson();
expect($value)->toBeGreaterThan(10);
expect($value)->toBeGreaterThanOrEqual(10);
expect($value)->toBeLessThan(10);
expect($value)->toBeLessThanOrEqual(10);

?>
