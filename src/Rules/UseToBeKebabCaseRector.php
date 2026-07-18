<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeKebabCaseRector extends AbstractStrCaseRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts Str::kebab() equality checks to toBeKebabCase() matcher (requires illuminate/support)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(Str::kebab($value) === $value)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeKebabCase();
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    protected function getStrMethodName(): string
    {
        return 'kebab';
    }

    protected function getMatcherName(): string
    {
        return 'toBeKebabCase';
    }
}
