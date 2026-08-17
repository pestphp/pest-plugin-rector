<?php

/**
 * @var string $path
 */
// Test is_writeable alias
expect(is_writeable($path))->toBeTrue();

-----
<?php

/**
 * @var string $path
 */
// Test is_writeable alias
expect($path)->toBeWritable();
