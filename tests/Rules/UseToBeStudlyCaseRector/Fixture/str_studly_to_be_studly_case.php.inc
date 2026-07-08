<?php

use Illuminate\Support\Str;

expect(Str::studly($value) === $value)->toBeTrue();
expect($value === Str::studly($value))->toBeTrue();

?>
-----
<?php

use Illuminate\Support\Str;

expect($value)->toBeStudlyCase();
expect($value)->toBeStudlyCase();

?>
