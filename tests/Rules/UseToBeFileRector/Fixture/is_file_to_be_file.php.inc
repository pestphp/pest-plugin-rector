<?php

expect(is_file($path))->toBeTrue();
expect(is_file('/tmp/file.txt'))->toBeTrue();
expect(is_file($missing))->toBeFalse();

?>
-----
<?php

expect($path)->toBeFile();
expect('/tmp/file.txt')->toBeFile();
expect($missing)->not->toBeFile();

?>
