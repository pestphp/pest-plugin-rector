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

final class UseToContainRector extends AbstractRector
{
    use ExpectChainValidation;

    private const string FUNCTION_NAME = 'in_array';

    private const string MATCHER_NAME = 'toContain';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts in_array() checks to toContain() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(in_array($item, $array))->toBeTrue();
expect(in_array($item, $array, true))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($array)->toContain($item);
expect($array)->toContain($item);
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

        if (count($funcCall->args) < 2) {
            return null;
        }

        $needleArg = $funcCall->args[0];
        $haystackArg = $funcCall->args[1];

        if (! $needleArg instanceof Arg || ! $haystackArg instanceof Arg) {
            return null;
        }

        if (isset($funcCall->args[2]) && $funcCall->args[2] instanceof Arg && $this->isFalse($funcCall->args[2]->value)) {
            return null;
        }

        if ($this->getType($haystackArg->value)->isArray()->no()) {
            return null;
        }

        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $haystackArg->value,
            self::MATCHER_NAME,
            [new Arg($needleArg->value)],
            $needsNot
        );
    }
}
