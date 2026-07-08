<?php

use Tests\Fixture\SampleTestCase;

// Class should be converted to extend()
pest()->uses(SampleTestCase::class);

?>
-----
<?php

use Tests\Fixture\SampleTestCase;

// Class should be converted to extend()
pest()->extend(SampleTestCase::class);

?>
