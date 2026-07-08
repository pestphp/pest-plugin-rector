<?php

// Test toBeFalse pattern
expect(is_dir($path))->toBeFalse();

-----
<?php

// Test toBeFalse pattern
expect($path)->not->toBeDirectory();
