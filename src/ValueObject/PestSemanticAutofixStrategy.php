<?php

declare(strict_types=1);

namespace Pest\Rector\ValueObject;

final class PestSemanticAutofixStrategy
{
    public const string NONE = 'none';

    public const string DIRECT_REWRITE = 'direct-rewrite';

    public const string LITERAL_NORMALIZATION = 'literal-normalization';

    public const string CHAIN_CLEANUP = 'chain-cleanup';

    public const string REVIEW_HINT = 'review-hint';
}
