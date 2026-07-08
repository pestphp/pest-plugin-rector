<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

final class PestSemanticSafetyLevel
{
    public const SAFE = 'safe';

    public const CONSERVATIVE = 'conservative';

    public const REVIEW_REQUIRED = 'review-required';
}
