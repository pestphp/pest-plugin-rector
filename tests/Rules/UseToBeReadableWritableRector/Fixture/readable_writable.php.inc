<?php

expect(is_readable($path))->toBeTrue();
expect(is_writable($file))->toBeTrue();
expect(is_writeable($dir))->toBeTrue();
expect(is_readable($locked))->toBeFalse();

?>
-----
<?php

expect($path)->toBeReadable();
expect($file)->toBeWritable();
expect($dir)->toBeWritable();
expect($locked)->not->toBeReadable();

?>
