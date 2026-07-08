<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts PHPUnit assertion method calls to Pest expect() chains
 */
final class ConvertAssertToExpectRector extends AbstractRector
{
    /**
     * Assertions with (actual) → expect(actual)->matcher()
     *
     * @var array<string, array{matcher: string, negated: bool}>
     */
    private const SINGLE_ARG_ASSERTIONS = [
        'assertTrue' => ['matcher' => 'toBeTrue', 'negated' => false],
        'assertFalse' => ['matcher' => 'toBeFalse', 'negated' => false],
        'assertNull' => ['matcher' => 'toBeNull', 'negated' => false],
        'assertNotNull' => ['matcher' => 'toBeNull', 'negated' => true],
        'assertEmpty' => ['matcher' => 'toBeEmpty', 'negated' => false],
        'assertNotEmpty' => ['matcher' => 'toBeEmpty', 'negated' => true],
        'assertIsArray' => ['matcher' => 'toBeArray', 'negated' => false],
        'assertIsList' => ['matcher' => 'toBeList', 'negated' => false],
        'assertIsBool' => ['matcher' => 'toBeBool', 'negated' => false],
        'assertIsFloat' => ['matcher' => 'toBeFloat', 'negated' => false],
        'assertIsInt' => ['matcher' => 'toBeInt', 'negated' => false],
        'assertIsString' => ['matcher' => 'toBeString', 'negated' => false],
        'assertIsNumeric' => ['matcher' => 'toBeNumeric', 'negated' => false],
        'assertIsObject' => ['matcher' => 'toBeObject', 'negated' => false],
        'assertIsCallable' => ['matcher' => 'toBeCallable', 'negated' => false],
        'assertIsIterable' => ['matcher' => 'toBeIterable', 'negated' => false],
        'assertIsScalar' => ['matcher' => 'toBeScalar', 'negated' => false],
        'assertIsResource' => ['matcher' => 'toBeResource', 'negated' => false],
        'assertNan' => ['matcher' => 'toBeNan', 'negated' => false],
        'assertInfinite' => ['matcher' => 'toBeInfinite', 'negated' => false],
        'assertFileExists' => ['matcher' => 'toBeFile', 'negated' => false],
        'assertDirectoryExists' => ['matcher' => 'toBeDirectory', 'negated' => false],
        'assertFileIsReadable' => ['matcher' => 'toBeReadableFile', 'negated' => false],
        'assertFileIsWritable' => ['matcher' => 'toBeWritableFile', 'negated' => false],
        'assertDirectoryIsReadable' => ['matcher' => 'toBeReadableDirectory', 'negated' => false],
        'assertDirectoryIsWritable' => ['matcher' => 'toBeWritableDirectory', 'negated' => false],
        'assertJson' => ['matcher' => 'toBeJson', 'negated' => false],
        'assertIsNotArray' => ['matcher' => 'toBeArray', 'negated' => true],
        'assertIsNotBool' => ['matcher' => 'toBeBool', 'negated' => true],
        'assertIsNotFloat' => ['matcher' => 'toBeFloat', 'negated' => true],
        'assertIsNotInt' => ['matcher' => 'toBeInt', 'negated' => true],
        'assertIsNotString' => ['matcher' => 'toBeString', 'negated' => true],
        'assertIsNotNumeric' => ['matcher' => 'toBeNumeric', 'negated' => true],
        'assertIsNotObject' => ['matcher' => 'toBeObject', 'negated' => true],
        'assertIsNotCallable' => ['matcher' => 'toBeCallable', 'negated' => true],
        'assertIsNotIterable' => ['matcher' => 'toBeIterable', 'negated' => true],
        'assertIsNotScalar' => ['matcher' => 'toBeScalar', 'negated' => true],
        'assertIsNotResource' => ['matcher' => 'toBeResource', 'negated' => true],
        'assertNotTrue' => ['matcher' => 'toBeTrue', 'negated' => true],
        'assertNotFalse' => ['matcher' => 'toBeFalse', 'negated' => true],
    ];

    /**
     * Assertions with (expected, actual) → expect(actual)->matcher(expected)
     *
     * @var array<string, array{matcher: string, negated: bool}>
     */
    private const TWO_ARG_ASSERTIONS = [
        'assertEquals' => ['matcher' => 'toEqual', 'negated' => false],
        'assertEqualsCanonicalizing' => ['matcher' => 'toEqualCanonicalizing', 'negated' => false],
        'assertNotEquals' => ['matcher' => 'toEqual', 'negated' => true],
        'assertSame' => ['matcher' => 'toBe', 'negated' => false],
        'assertNotSame' => ['matcher' => 'toBe', 'negated' => true],
        'assertCount' => ['matcher' => 'toHaveCount', 'negated' => false],
        'assertSameSize' => ['matcher' => 'toHaveSameSize', 'negated' => false],
        'assertInstanceOf' => ['matcher' => 'toBeInstanceOf', 'negated' => false],
        'assertNotInstanceOf' => ['matcher' => 'toBeInstanceOf', 'negated' => true],
        'assertContains' => ['matcher' => 'toContain', 'negated' => false],
        // PHPUnit's assertContainsEquals() uses loose-comparison semantics, so it maps to
        // Pest's toContainEqual() instead of the stricter toContain() used by UseToContainRector.
        'assertContainsEquals' => ['matcher' => 'toContainEqual', 'negated' => false],
        'assertNotContains' => ['matcher' => 'toContain', 'negated' => true],
        'assertNotContainsEquals' => ['matcher' => 'toContainEqual', 'negated' => true],
        'assertContainsOnlyInstancesOf' => ['matcher' => 'toContainOnlyInstancesOf', 'negated' => false],
        'assertArrayHasKey' => ['matcher' => 'toHaveKey', 'negated' => false],
        'assertArrayNotHasKey' => ['matcher' => 'toHaveKey', 'negated' => true],
        'assertObjectHasProperty' => ['matcher' => 'toHaveProperty', 'negated' => false],
        'assertObjectNotHasProperty' => ['matcher' => 'toHaveProperty', 'negated' => true],
        'assertStringContainsString' => ['matcher' => 'toContain', 'negated' => false],
        'assertStringNotContainsString' => ['matcher' => 'toContain', 'negated' => true],
        'assertStringStartsWith' => ['matcher' => 'toStartWith', 'negated' => false],
        'assertStringEndsWith' => ['matcher' => 'toEndWith', 'negated' => false],
        'assertMatchesRegularExpression' => ['matcher' => 'toMatch', 'negated' => false],
        'assertGreaterThan' => ['matcher' => 'toBeGreaterThan', 'negated' => false],
        'assertGreaterThanOrEqual' => ['matcher' => 'toBeGreaterThanOrEqual', 'negated' => false],
        'assertLessThan' => ['matcher' => 'toBeLessThan', 'negated' => false],
        'assertLessThanOrEqual' => ['matcher' => 'toBeLessThanOrEqual', 'negated' => false],
    ];

    /**
     * Assertions with (expected, actual, extra) → expect(actual)->matcher(expected, extra)
     *
     * @var array<string, array{matcher: string, negated: bool}>
     */
    private const THREE_ARG_ASSERTIONS = [
        'assertEqualsWithDelta' => ['matcher' => 'toEqualWithDelta', 'negated' => false],
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts PHPUnit assertion method calls to Pest expect() chains',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$this->assertEquals('expected', $result);
$this->assertTrue($value);
$this->assertCount(3, $items);
$this->assertNotNull($user);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($result)->toEqual('expected');
expect($value)->toBeTrue();
expect($items)->toHaveCount(3);
expect($user)->not->toBeNull();
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
$this->assertIsList($values);
$this->assertIsNotArray($value);
$this->assertIsNotBool($value);
$this->assertIsNotFloat($value);
$this->assertIsNotInt($value);
$this->assertIsNotString($value);
$this->assertIsNotNumeric($value);
$this->assertIsNotObject($value);
$this->assertIsNotCallable($value);
$this->assertIsNotIterable($value);
$this->assertIsNotScalar($value);
$this->assertIsNotResource($value);
$this->assertContainsOnlyInstancesOf(User::class, $users);
$this->assertSameSize($expected, $actual);
$this->assertObjectHasProperty('name', $user);
$this->assertObjectNotHasProperty('password', $user);
$this->assertEqualsCanonicalizing(['b', 'a'], $letters);
$this->assertEqualsWithDelta(10.5, $score, 0.1);
$this->assertContainsEquals(['id' => 1], $items);
$this->assertNotContainsEquals(['id' => 2], $items);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($values)->toBeList();
expect($value)->not->toBeArray();
expect($value)->not->toBeBool();
expect($value)->not->toBeFloat();
expect($value)->not->toBeInt();
expect($value)->not->toBeString();
expect($value)->not->toBeNumeric();
expect($value)->not->toBeObject();
expect($value)->not->toBeCallable();
expect($value)->not->toBeIterable();
expect($value)->not->toBeScalar();
expect($value)->not->toBeResource();
expect($users)->toContainOnlyInstancesOf(User::class);
expect($actual)->toHaveSameSize($expected);
expect($user)->toHaveProperty('name');
expect($user)->not->toHaveProperty('password');
expect($letters)->toEqualCanonicalizing(['b', 'a']);
expect($score)->toEqualWithDelta(10.5, 0.1);
expect($items)->toContainEqual(['id' => 1]);
expect($items)->not->toContainEqual(['id' => 2]);
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
        if (! $this->isThisCall($node)) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        $assertionName = $node->name->name;

        if (isset(self::SINGLE_ARG_ASSERTIONS[$assertionName])) {
            return $this->convertSingleArgAssertion($node, $assertionName);
        }

        if (isset(self::TWO_ARG_ASSERTIONS[$assertionName])) {
            return $this->convertTwoArgAssertion($node, $assertionName);
        }

        if (isset(self::THREE_ARG_ASSERTIONS[$assertionName])) {
            return $this->convertThreeArgAssertion($node, $assertionName);
        }

        return null;
    }

    private function isThisCall(MethodCall $node): bool
    {
        return $node->var instanceof Variable && $this->isName($node->var, 'this');
    }

    private function convertSingleArgAssertion(MethodCall $node, string $assertionName): ?MethodCall
    {
        $config = self::SINGLE_ARG_ASSERTIONS[$assertionName];

        if (count($node->args) < 1) {
            return null;
        }

        $firstArg = $node->args[0];
        if (! $firstArg instanceof Arg) {
            return null;
        }

        $expectCall = $this->createExpectCall($firstArg->value);

        return $this->buildResult($expectCall, $config['matcher'], [], $config['negated']);
    }

    private function convertTwoArgAssertion(MethodCall $node, string $assertionName): ?MethodCall
    {
        $config = self::TWO_ARG_ASSERTIONS[$assertionName];

        if (count($node->args) < 2) {
            return null;
        }

        $firstArg = $node->args[0];
        $secondArg = $node->args[1];

        if (! $firstArg instanceof Arg || ! $secondArg instanceof Arg) {
            return null;
        }

        $expectCall = $this->createExpectCall($secondArg->value);
        $matcherArgs = [new Arg($firstArg->value)];

        return $this->buildResult($expectCall, $config['matcher'], $matcherArgs, $config['negated']);
    }

    private function convertThreeArgAssertion(MethodCall $node, string $assertionName): ?MethodCall
    {
        $config = self::THREE_ARG_ASSERTIONS[$assertionName];

        if (count($node->args) < 3) {
            return null;
        }

        $firstArg = $node->args[0];
        $secondArg = $node->args[1];
        $thirdArg = $node->args[2];

        if (! $firstArg instanceof Arg || ! $secondArg instanceof Arg || ! $thirdArg instanceof Arg) {
            return null;
        }

        $expectCall = $this->createExpectCall($secondArg->value);
        $matcherArgs = [new Arg($firstArg->value), new Arg($thirdArg->value)];

        return $this->buildResult($expectCall, $config['matcher'], $matcherArgs, $config['negated']);
    }

    private function createExpectCall(Expr $value): FuncCall
    {
        return new FuncCall(new Name('expect'), [new Arg($value)]);
    }

    /**
     * Build the expect()->matcher() or expect()->not->matcher() chain
     *
     * @param array<Arg> $matcherArgs
     */
    private function buildResult(FuncCall $expectCall, string $matcher, array $matcherArgs, bool $negated): MethodCall
    {
        if ($negated) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, $matcher, $matcherArgs);
        }

        return new MethodCall($expectCall, $matcher, $matcherArgs);
    }
}
