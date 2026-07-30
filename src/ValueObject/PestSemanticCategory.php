<?php

declare(strict_types=1);

namespace Pest\Rector\ValueObject;

final class PestSemanticCategory
{
    public const string TEST_DEFINITION = 'test-definition';

    public const string EXPECTATION = 'expectation';

    public const string LIFECYCLE = 'lifecycle';

    public const string EXECUTION = 'execution';
}
