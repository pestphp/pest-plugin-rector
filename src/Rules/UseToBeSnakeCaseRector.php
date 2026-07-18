<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeSnakeCaseRector extends AbstractStrCaseRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts Str::snake() equality checks to toBeSnakeCase() matcher (requires illuminate/support)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(Str::snake($value) === $value)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeSnakeCase();
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    protected function getStrMethodName(): string
    {
        return 'snake';
    }

    protected function getMatcherName(): string
    {
        return 'toBeSnakeCase';
    }
}
