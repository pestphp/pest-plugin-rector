<?php

use Tests\Fixture\AnotherSampleTrait;
use Tests\Fixture\SampleTestCase;
use Tests\Fixture\SampleTrait;

// Mixed class and traits should be split
pest()->uses(SampleTestCase::class, SampleTrait::class, AnotherSampleTrait::class)->in('Feature');

?>
-----
<?php

use Tests\Fixture\AnotherSampleTrait;
use Tests\Fixture\SampleTestCase;
use Tests\Fixture\SampleTrait;

// Mixed class and traits should be split
pest()->extend(SampleTestCase::class)->use(SampleTrait::class, AnotherSampleTrait::class)->in('Feature');

?>
