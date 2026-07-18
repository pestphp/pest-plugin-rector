<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeSlugRector extends AbstractStrCaseRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts Str::slug() equality checks to toBeSlug() matcher (requires illuminate/support)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(Str::slug($value) === $value)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeSlug();
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    protected function getStrMethodName(): string
    {
        return 'slug';
    }

    protected function getMatcherName(): string
    {
        return 'toBeSlug';
    }
}
