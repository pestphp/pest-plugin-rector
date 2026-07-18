<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeStudlyCaseRector extends AbstractStrCaseRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts Str::studly() equality checks to toBeStudlyCase() matcher (requires illuminate/support)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(Str::studly($value) === $value)->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeStudlyCase();
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    protected function getStrMethodName(): string
    {
        return 'studly';
    }

    protected function getMatcherName(): string
    {
        return 'toBeStudlyCase';
    }
}
