<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use Pest\Rector\Concerns\ExpectChainValidation;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class UseToBeAlphaRector extends AbstractRector
{
    use ExpectChainValidation;

    private const string FUNCTION_NAME = 'ctype_alpha';

    private const string MATCHER_NAME = 'toBeAlpha';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts ctype_alpha() checks to toBeAlpha() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(ctype_alpha($value))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeAlpha();
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param  MethodCall  $node
     */
    public function refactor(Node $node): ?Node
    {
        $extracted = $this->extractFunctionFromExpect($node, [self::FUNCTION_NAME]);
        if ($extracted === null) {
            return null;
        }

        $funcCall = $extracted['funcCall'];
        if (count($funcCall->args) !== 1) {
            return null;
        }

        $ctypeArg = $funcCall->args[0];
        if (! $ctypeArg instanceof Arg) {
            return null;
        }

        if (! $this->getType($ctypeArg->value)->isString()->yes()) {
            return null;
        }

        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $ctypeArg->value,
            self::MATCHER_NAME,
            [],
            $needsNot
        );
    }
}
