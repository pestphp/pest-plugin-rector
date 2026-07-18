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

final class UseToBeReadableWritableRector extends AbstractRector
{
    use ExpectChainValidation;

    /**
     * @var array<string, string>
     */
    private const FUNCTION_MATCHERS = [
        'is_readable' => 'toBeReadable',
        'is_writable' => 'toBeWritable',
        'is_writeable' => 'toBeWritable',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts is_readable()/is_writable() checks to toBeReadable()/toBeWritable() matchers (requires the custom expect()->extend() matchers of the same name; not registered in any set)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(is_readable($path))->toBeTrue();
expect(is_writable($file))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($path)->toBeReadable();
expect($file)->toBeWritable();
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
        $extracted = $this->extractFunctionFromExpect($node, array_keys(self::FUNCTION_MATCHERS));
        if ($extracted === null) {
            return null;
        }

        $funcCall = $extracted['funcCall'];
        if (count($funcCall->args) !== 1) {
            return null;
        }

        $pathArg = $funcCall->args[0];
        if (! $pathArg instanceof Arg) {
            return null;
        }

        if ($this->getType($pathArg->value)->isString()->no()) {
            return null;
        }

        $funcName = $this->getName($funcCall);
        if ($funcName === null) {
            return null;
        }

        $matcherMethod = self::FUNCTION_MATCHERS[$funcName];
        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $pathArg->value,
            $matcherMethod,
            [],
            $needsNot
        );
    }
}
