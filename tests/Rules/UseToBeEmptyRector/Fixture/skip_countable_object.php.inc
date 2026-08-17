<?php

namespace Tests\Rules\UseToBeEmptyRector\Fixture;

use Countable;

class EmptyCollection implements Countable
{
    public function count(): int
    {
        return 0;
    }
}

$collection = new EmptyCollection();

// `empty($object)` is always false, while `toBeEmpty()` runs through
// Assert::assertEmpty(), which reports a Countable holding zero items as empty.
// Rewriting this turns a failing assertion into a passing one, so the rule must
// leave every object subject alone.
expect(empty($collection))->toBeTrue();
