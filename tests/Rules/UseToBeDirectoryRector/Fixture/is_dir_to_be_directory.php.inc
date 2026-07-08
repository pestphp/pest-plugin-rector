<?php

expect(is_dir($path))->toBeTrue();
expect(is_dir('/tmp'))->toBeTrue();
expect(is_dir($missing))->toBeFalse();

?>
-----
<?php

expect($path)->toBeDirectory();
expect('/tmp')->toBeDirectory();
expect($missing)->not->toBeDirectory();

?>
