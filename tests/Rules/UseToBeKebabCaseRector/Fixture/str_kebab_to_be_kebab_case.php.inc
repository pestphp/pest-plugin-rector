<?php

use Illuminate\Support\Str;

expect(Str::kebab($value) === $value)->toBeTrue();
expect($value === Str::kebab($value))->toBeTrue();

?>
-----
<?php

use Illuminate\Support\Str;

expect($value)->toBeKebabCase();
expect($value)->toBeKebabCase();

?>
