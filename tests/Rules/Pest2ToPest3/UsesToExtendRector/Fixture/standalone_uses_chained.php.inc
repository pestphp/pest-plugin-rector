<?php

use Tests\Fixture\AnotherSampleTrait;
use Tests\Fixture\SampleTestCase;
use Tests\Fixture\SampleTrait;

// Standalone uses() with chain should be converted
uses(SampleTestCase::class, SampleTrait::class, AnotherSampleTrait::class)->in('Feature');

?>
-----
<?php

use Tests\Fixture\AnotherSampleTrait;
use Tests\Fixture\SampleTestCase;
use Tests\Fixture\SampleTrait;

// Standalone uses() with chain should be converted
pest()->extend(SampleTestCase::class)->use(SampleTrait::class, AnotherSampleTrait::class)->in('Feature');

?>
