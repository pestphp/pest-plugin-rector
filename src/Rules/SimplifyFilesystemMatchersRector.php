<?php

declare(strict_types=1);

namespace Pest\Rector\Rules;

use Pest\Rector\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class SimplifyFilesystemMatchersRector extends AbstractRector
{
    /**
     * @var array<string, array<string, string>>
     */
    private const COMBINED_FUNCTION_MATCHERS = [
        'is_dir' => [
            'is_readable' => 'toBeReadableDirectory',
            'is_writable' => 'toBeWritableDirectory',
            'is_writeable' => 'toBeWritableDirectory',
        ],
        'is_file' => [
            'is_readable' => 'toBeReadableFile',
            'is_writable' => 'toBeWritableFile',
            'is_writeable' => 'toBeWritableFile',
        ],
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private const COMBINED_CHAIN_MATCHERS = [
        'toBeDirectory' => [
            'toBeReadable' => 'toBeReadableDirectory',
            'toBeWritable' => 'toBeWritableDirectory',
        ],
        'toBeFile' => [
            'toBeReadable' => 'toBeReadableFile',
            'toBeWritable' => 'toBeWritableFile',
        ],
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplifies combined filesystem checks to single Pest matchers',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(is_file($path) && is_readable($path))->toBeTrue();
expect($path)->toBeFile()->toBeReadable();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($path)->toBeReadableFile();
expect($path)->toBeReadableFile();
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
        if (! $this->isExpectChain($node)) {
            return null;
        }

        return $this->refactorBooleanAnd($node) ?? $this->refactorChainedMatchers($node);
    }

    private function refactorBooleanAnd(MethodCall $node): ?Node
    {
        if (! $this->isName($node->name, 'toBeTrue')) {
            return null;
        }

        $expectCall = $this->getMatcherSubjectHolder($node);
        if (! $expectCall instanceof FuncCall && ! $expectCall instanceof MethodCall) {
            return null;
        }

        $expectArg = $this->getMatcherSubject($node);
        if (! $expectArg instanceof BooleanAnd) {
            return null;
        }

        $left = $expectArg->left;
        $right = $expectArg->right;

        if (! $left instanceof FuncCall || ! $right instanceof FuncCall) {
            return null;
        }

        $leftName = $this->getName($left);
        $rightName = $this->getName($right);

        if ($leftName === null || $rightName === null) {
            return null;
        }

        if (count($left->args) !== 1 || count($right->args) !== 1) {
            return null;
        }

        $leftArg = $left->args[0];
        $rightArg = $right->args[0];

        if (! $leftArg instanceof Arg || ! $rightArg instanceof Arg) {
            return null;
        }

        if (! $this->nodeComparator->areNodesEqual($leftArg->value, $rightArg->value)) {
            return null;
        }

        $matcher = $this->findCombinedFunctionMatcher($leftName, $rightName);
        if ($matcher === null) {
            return null;
        }

        $expectCall->args = [new Arg($leftArg->value)];
        $node->name = new Identifier($matcher);
        $node->args = [];
        $node->var = $expectCall;

        return $node;
    }

    private function refactorChainedMatchers(MethodCall $node): ?Node
    {
        $outerName = $this->getName($node->name);
        if ($outerName === null) {
            return null;
        }

        if (! in_array($outerName, ['toBeReadable', 'toBeWritable'], true)) {
            return null;
        }

        $inner = $node->var;
        if (! $inner instanceof MethodCall) {
            return null;
        }

        $innerName = $this->getName($inner->name);
        if ($innerName === null) {
            return null;
        }

        if (! isset(self::COMBINED_CHAIN_MATCHERS[$innerName][$outerName])) {
            return null;
        }

        if ($node->args !== [] || $inner->args !== []) {
            return null;
        }

        $combinedMatcher = self::COMBINED_CHAIN_MATCHERS[$innerName][$outerName];

        $inner->name = new Identifier($combinedMatcher);

        return $inner;
    }

    private function findCombinedFunctionMatcher(string $func1, string $func2): ?string
    {
        return self::COMBINED_FUNCTION_MATCHERS[$func1][$func2] ?? self::COMBINED_FUNCTION_MATCHERS[$func2][$func1] ?? null;
    }
}
