<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

final class PestSemanticAutofixStrategy
{
    public const NONE = 'none';

    public const DIRECT_REWRITE = 'direct-rewrite';

    public const LITERAL_NORMALIZATION = 'literal-normalization';

    public const CHAIN_CLEANUP = 'chain-cleanup';

    public const REVIEW_HINT = 'review-hint';
}
