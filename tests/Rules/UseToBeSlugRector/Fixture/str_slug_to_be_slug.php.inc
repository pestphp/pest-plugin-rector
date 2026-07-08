<?php

use Illuminate\Support\Str;

expect(Str::slug($value) === $value)->toBeTrue();
expect($value === Str::slug($value))->toBeTrue();

?>
-----
<?php

use Illuminate\Support\Str;

expect($value)->toBeSlug();
expect($value)->toBeSlug();

?>
