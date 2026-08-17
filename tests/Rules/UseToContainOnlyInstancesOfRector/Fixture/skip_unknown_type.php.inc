<?php

namespace Tests\Rules\UseToContainOnlyInstancesOfRector\Fixture;

class UnknownTypeUser {}

// The subject has no resolvable type, so the rewrite is not proven safe and the
// rule must leave the code alone.
expect($items)->each->toBeInstanceOf(UnknownTypeUser::class);
