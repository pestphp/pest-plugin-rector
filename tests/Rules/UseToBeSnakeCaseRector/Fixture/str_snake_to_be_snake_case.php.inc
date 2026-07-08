<?php

use Illuminate\Support\Str;

expect(Str::snake($value) === $value)->toBeTrue();
expect($value === Str::snake($value))->toBeTrue();

?>
-----
<?php

use Illuminate\Support\Str;

expect($value)->toBeSnakeCase();
expect($value)->toBeSnakeCase();

?>
