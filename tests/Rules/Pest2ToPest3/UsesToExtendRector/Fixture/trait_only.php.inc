<?php

use Tests\Fixture\SampleTrait;

// Trait should be converted to use()
pest()->uses(SampleTrait::class);

?>
-----
<?php

use Tests\Fixture\SampleTrait;

// Trait should be converted to use()
pest()->use(SampleTrait::class);

?>
