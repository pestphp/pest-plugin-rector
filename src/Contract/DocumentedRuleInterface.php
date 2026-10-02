<?php

declare(strict_types=1);

namespace Pest\Rector\Contract;

use Pest\Rector\ValueObject\RuleDefinition;

interface DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition;
}
