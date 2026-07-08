<?php

use Illuminate\Support\Str;

expect(Str::camel($value) === $value)->toBeTrue();
expect($value === Str::camel($value))->toBeTrue();

?>
-----
<?php

use Illuminate\Support\Str;

expect($value)->toBeCamelCase();
expect($value)->toBeCamelCase();

?>
