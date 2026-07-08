<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixture\AnotherSampleTrait;

uses(
    RefreshDatabase::class,
    AnotherSampleTrait::class,
);
-----
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixture\AnotherSampleTrait;

uses(
    AnotherSampleTrait::class,
);
