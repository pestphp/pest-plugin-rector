<?php

// Test toBeFalse pattern
expect(is_file($path))->toBeFalse();

-----
<?php

// Test toBeFalse pattern
expect($path)->not->toBeFile();
