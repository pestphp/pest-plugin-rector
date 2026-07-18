<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeCamelCaseRector extends AbstractStrCaseRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts Str::camel() equality checks to toBeCamelCase() matcher (requires illuminate/support)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(Str::camel($value) === $value)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeCamelCase();
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    protected function getStrMethodName(): string
    {
        return 'camel';
    }

    protected function getMatcherName(): string
    {
        return 'toBeCamelCase';
    }
}
