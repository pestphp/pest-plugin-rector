<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts preg_match assertions to toMatch() matcher
 */
final class UseToMatchRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts expect(preg_match("/pattern/", $str))->toBe(1) to expect($str)->toMatch("/pattern/")',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(preg_match('/pattern/', $string))->toBe(1);
expect(preg_match('/^hello/', $text))->toEqual(1);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($string)->toMatch('/pattern/');
expect($text)->toMatch('/^hello/');
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isExpectChain($node)) {
            return null;
        }

        if (! $this->isNames($node->name, ['toBe', 'toEqual'])) {
            return null;
        }

        if (count($node->args) !== 1) {
            return null;
        }

        $methodArg = $node->args[0];
        if (! $methodArg instanceof Arg) {
            return null;
        }

        if (! $methodArg->value instanceof Int_ || $methodArg->value->value !== 1) {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        $expectArg = $this->getExpectArgument($node);
        if (! $expectArg instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($expectArg, 'preg_match')) {
            return null;
        }

        if (count($expectArg->args) < 2) {
            return null;
        }

        $patternArg = $expectArg->args[0];
        $subjectArg = $expectArg->args[1];

        if (! $patternArg instanceof Arg || ! $subjectArg instanceof Arg) {
            return null;
        }

        if ($this->getType($subjectArg->value)->isString()->no()) {
            return null;
        }

        $expectCall->args = [new Arg($subjectArg->value)];
        $node->name = new Identifier('toMatch');
        $node->args = [new Arg($patternArg->value)];

        return $node;
    }
}
