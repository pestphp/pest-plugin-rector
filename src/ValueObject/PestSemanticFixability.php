<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

final class PestSemanticFixability
{
    public const AUTO_FIXABLE = 'auto-fixable';

    public const ASSISTED = 'assisted';

    public const PLANNED = 'planned';

    public const INFORMATIONAL = 'informational';
}
