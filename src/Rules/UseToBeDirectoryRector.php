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

final class UseToBeDirectoryRector extends AbstractRector
{
    use ExpectChainValidation;

    private const string FUNCTION_NAME = 'is_dir';

    private const string MATCHER_NAME = 'toBeDirectory';

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts is_dir() checks to toBeDirectory() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(is_dir($path))->toBeTrue();
expect(is_dir('/tmp'))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($path)->toBeDirectory();
expect('/tmp')->toBeDirectory();
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

        $pathArg = $funcCall->args[0];
        if (! $pathArg instanceof Arg) {
            return null;
        }

        if ($this->getType($pathArg->value)->isString()->no()) {
            return null;
        }

        $needsNot = $this->calculateNeedsNot($extracted['methodName'], $node);

        return $this->buildMatcherCall(
            $extracted['expectCall'],
            $pathArg->value,
            self::MATCHER_NAME,
            [],
            $needsNot
        );
    }
}
