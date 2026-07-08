<?php

expect(is_file($path) && is_readable($path))->toBeTrue();
expect(is_file($path) && is_writable($path))->toBeTrue();
expect(is_dir($path) && is_readable($path))->toBeTrue();
expect(is_dir($path) && is_writable($path))->toBeTrue();

// Reversed order should also work
expect(is_readable($path) && is_file($path))->toBeTrue();
expect(is_writable($path) && is_dir($path))->toBeTrue();

// Alias is_writeable
expect(is_file($path) && is_writeable($path))->toBeTrue();

?>
-----
<?php

expect($path)->toBeReadableFile();
expect($path)->toBeWritableFile();
expect($path)->toBeReadableDirectory();
expect($path)->toBeWritableDirectory();

// Reversed order should also work
expect($path)->toBeReadableFile();
expect($path)->toBeWritableDirectory();

// Alias is_writeable
expect($path)->toBeWritableFile();

?>
