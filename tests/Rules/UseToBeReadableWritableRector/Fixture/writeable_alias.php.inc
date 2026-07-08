<?php

// Test is_writeable alias
expect(is_writeable($path))->toBeTrue();

-----
<?php

// Test is_writeable alias
expect($path)->toBeWritable();
