<?php

declare(strict_types=1);

namespace Pest\Rector\ValueObject;

final class PestSemanticSafetyLevel
{
    public const string SAFE = 'safe';

    public const string CONSERVATIVE = 'conservative';

    public const string REVIEW_REQUIRED = 'review-required';
}
