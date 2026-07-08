<?php

// All type functions
expect(is_array($value))->toBeTrue();
expect(is_string($value))->toBeTrue();
expect(is_int($value))->toBeTrue();
expect(is_integer($value))->toBeTrue();
expect(is_float($value))->toBeTrue();
expect(is_double($value))->toBeTrue();
expect(is_bool($value))->toBeTrue();
expect(is_numeric($value))->toBeTrue();
expect(is_callable($value))->toBeTrue();
expect(is_iterable($value))->toBeTrue();
expect(is_object($value))->toBeTrue();
expect(is_resource($value))->toBeTrue();
expect(is_scalar($value))->toBeTrue();
expect(is_null($value))->toBeTrue();

-----
<?php

// All type functions
expect($value)->toBeArray();
expect($value)->toBeString();
expect($value)->toBeInt();
expect($value)->toBeInt();
expect($value)->toBeFloat();
expect($value)->toBeFloat();
expect($value)->toBeBool();
expect($value)->toBeNumeric();
expect($value)->toBeCallable();
expect($value)->toBeIterable();
expect($value)->toBeObject();
expect($value)->toBeResource();
expect($value)->toBeScalar();
expect($value)->toBeNull();
