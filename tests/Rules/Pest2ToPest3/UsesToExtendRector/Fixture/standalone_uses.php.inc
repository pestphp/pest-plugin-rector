<?php

use Tests\Fixture\SampleTestCase;

// Standalone uses() should be converted to pest()->extend()
uses(SampleTestCase::class);

?>
-----
<?php

use Tests\Fixture\SampleTestCase;

// Standalone uses() should be converted to pest()->extend()
pest()->extend(SampleTestCase::class);

?>
