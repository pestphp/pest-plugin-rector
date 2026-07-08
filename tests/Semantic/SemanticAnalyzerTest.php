<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use RectorPest\Analyzer\HookSemanticAnalyzer;
use RectorPest\Analyzer\PestChainAnalyzer;
use RectorPest\Analyzer\SemanticExpectationAnalyzer;
use RectorPest\Support\PestFunctionDetector;

it('finds invalid describe hooks recursively', function (): void {
    $describeCall = findFirstNode(
        <<<'PHP'
describe('users', function (): void {
    beforeAll(function (): void {});

    describe('nested', function (): void {
        afterAll(function (): void {});
        beforeEach(function (): void {});
    });
});
PHP,
        static fn (Node $node): bool => $node instanceof FuncCall && PestFunctionDetector::isDescribeFunction($node),
    );

    $closure = PestFunctionDetector::extractClosure($describeCall);

    expect($closure)->toBeInstanceOf(Closure::class);

    $invalidHooks = HookSemanticAnalyzer::findInvalidDescribeHooks($closure, [
        'beforeAll' => 'beforeEach',
        'afterAll' => 'afterEach',
    ]);

    expect(array_map(
        PestFunctionDetector::getFunctionName(...),
        $invalidHooks,
    ))->toBe(['beforeAll', 'afterAll']);
});

it('identifies Pest test and expect chains', function (): void {
    $repeatCall = findFirstNode(
        <<<'PHP'
it('retries', function (): void {
    expect('pest')->toBeString();
})->group('slow')->repeat(0);
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'repeat',
    );

    $expectCall = findFirstNode(
        <<<'PHP'
expect('pest')->not->toBeString();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeString',
    );

    expect(PestChainAnalyzer::isPestTestChain($repeatCall))->toBeTrue();
    expect(PestChainAnalyzer::isExpectChain($expectCall))->toBeTrue();
    expect(PestChainAnalyzer::hasNotModifier($expectCall))->toBeTrue();
    expect(PestChainAnalyzer::getExpectArgument($expectCall))->toBeInstanceOf(String_::class);
});

it('classifies literal type expectations conservatively', function (): void {
    $redundantMatcher = findFirstNode(
        <<<'PHP'
expect('pest')->toBeString();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeString',
    );

    $impossibleMatcher = findFirstNode(
        <<<'PHP'
expect('pest')->toBeInt();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeInt',
    );

    $negatedRedundantMatcher = findFirstNode(
        <<<'PHP'
expect(123)->not->toBeString();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeString',
    );

    $indirectMatcher = findFirstNode(
        <<<'PHP'
expect('["pest"]')->json()->toBeArray();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeArray',
    );

    $classStringMatcher = findFirstNode(
        <<<'PHP'
expect(ExampleAttribute::class)->toBeString();
PHP,
        static fn (Node $node): bool => $node instanceof MethodCall && $node->name instanceof Identifier && $node->name->toString() === 'toBeString',
    );

    $redundantAnalysis = SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($redundantMatcher);
    $impossibleAnalysis = SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($impossibleMatcher);
    $negatedRedundantAnalysis = SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($negatedRedundantMatcher);
    $classStringAnalysis = SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($classStringMatcher);

    expect($redundantAnalysis?->expectedCategory)->toBe('string');
    expect($redundantAnalysis?->literalCategory)->toBe('string');
    expect($redundantAnalysis?->isRedundant())->toBeTrue();

    expect($impossibleAnalysis?->expectedCategory)->toBe('int');
    expect($impossibleAnalysis?->literalCategory)->toBe('string');
    expect($impossibleAnalysis?->isImpossible())->toBeTrue();

    expect($negatedRedundantAnalysis?->negated)->toBeTrue();
    expect($negatedRedundantAnalysis?->isRedundant())->toBeTrue();

    expect($classStringAnalysis?->literalCategory)->toBe('string');
    expect($classStringAnalysis?->isRedundant())->toBeTrue();

    expect(SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($indirectMatcher))
        ->toBeNull();
});

it('distinguishes callback trees that require instance binding', function (): void {
    $testCallback = findFirstNode(
        <<<'PHP'
it('uses nested closures', static function (): void {
    $factory = function (): Closure {
        return function (): string {
            return $this->app->getLocale();
        };
    };

    expect($factory()())->toBeString();
});
PHP,
        static fn (Node $node): bool => $node instanceof FuncCall && PestFunctionDetector::isTestFunction($node),
    );

    $describeCallback = findFirstNode(
        <<<'PHP'
describe('hooks', static function (): void {
    beforeEach(static function (): void {
        $resolver = fn (): string => $this->app->getLocale();

        expect($resolver())->toBeString();
    });
});
PHP,
        static fn (Node $node): bool => $node instanceof FuncCall && PestFunctionDetector::isDescribeFunction($node),
    );

    $beforeEachCallback = findFirstNode(
        <<<'PHP'
describe('hooks', static function (): void {
    beforeEach(static function (): void {
        $resolver = fn (): string => $this->app->getLocale();

        expect($resolver())->toBeString();
    });
});
PHP,
        static fn (Node $node): bool => $node instanceof FuncCall && PestFunctionDetector::getFunctionName($node) === 'beforeEach',
    );

    expect(PestFunctionDetector::closureRequiresInstanceBinding(PestFunctionDetector::extractClosure($testCallback)))
        ->toBeTrue();

    expect(PestFunctionDetector::closureRequiresInstanceBinding(PestFunctionDetector::extractClosure($describeCallback)))
        ->toBeFalse();

    expect(PestFunctionDetector::closureRequiresInstanceBinding(PestFunctionDetector::extractClosure($beforeEachCallback)))
        ->toBeTrue();
});

#[Attribute]
final class ExampleAttribute
{
}

/**
 * @param callable(Node): bool $filter
 */
function findFirstNode(string $code, callable $filter): Node
{
    $parser = (new ParserFactory())->createForNewestSupportedVersion();
    $nodeFinder = new NodeFinder();
    $nodes = $parser->parse("<?php\n\n" . $code);

    expect($nodes)->not->toBeNull();

    $node = $nodeFinder->findFirst($nodes, $filter);

    expect($node)->toBeInstanceOf(Node::class);

    return $node;
}
