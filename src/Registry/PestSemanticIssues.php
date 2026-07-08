<?php

declare(strict_types=1);

namespace RectorPest\Registry;

use RectorPest\ValueObject\PestSemanticAutofixStrategy;
use RectorPest\ValueObject\PestSemanticCategory;
use RectorPest\ValueObject\PestSemanticConfidence;
use RectorPest\ValueObject\PestSemanticFixability;
use RectorPest\ValueObject\PestSemanticFixCategory;
use RectorPest\ValueObject\PestSemanticIssue;
use RectorPest\ValueObject\PestSemanticSafetyLevel;
use RectorPest\ValueObject\PestSemanticSeverity;

/**
 * Canonical semantic issue registry for rector-pest and future PestStan interoperability.
 */
final class PestSemanticIssues
{
    public const STATIC_TEST_CLOSURE = 'pest.test.staticClosure';

    public const INVALID_REPEAT_VALUE = 'pest.execution.invalidRepeatValue';

    public const BEFORE_ALL_IN_DESCRIBE = 'pest.lifecycle.beforeAllInDescribe';

    public const AFTER_ALL_IN_DESCRIBE = 'pest.lifecycle.afterAllInDescribe';

    public const EMPTY_TEST_CLOSURE = 'pest.test.emptyClosure';

    public const REDUNDANT_EXPECTATION = 'pest.expectation.redundant';

    public const IMPOSSIBLE_EXPECTATION = 'pest.expectation.impossible';

    /** @var array<string, PestSemanticIssue>|null */
    private static ?array $issues = null;

    /**
     * @return array<string, PestSemanticIssue>
     */
    private static function issues(): array
    {
        if (is_array(self::$issues)) {
            return self::$issues;
        }

        self::$issues = [
            self::STATIC_TEST_CLOSURE => new PestSemanticIssue(
                self::STATIC_TEST_CLOSURE,
                'Test closure passed to a Pest function must not be static.',
                [self::STATIC_TEST_CLOSURE, 'pest.staticTestClosure'],
                PestSemanticCategory::TEST_DEFINITION,
                PestSemanticFixCategory::CLEANUP,
                PestSemanticFixability::AUTO_FIXABLE,
                PestSemanticSeverity::WARNING,
                PestSemanticSafetyLevel::CONSERVATIVE,
                PestSemanticConfidence::HIGH,
                ['pest', 'test', 'closure', 'binding'],
                null,
                PestSemanticAutofixStrategy::DIRECT_REWRITE,
                PestSemanticIssue::INTEROPERABILITY_VERSION,
                'test-definition',
                'closure-binding',
            ),
            self::INVALID_REPEAT_VALUE => new PestSemanticIssue(
                self::INVALID_REPEAT_VALUE,
                'repeat() requires a value greater than 0.',
                [self::INVALID_REPEAT_VALUE, 'pest.repeatInvalidValue'],
                PestSemanticCategory::EXECUTION,
                PestSemanticFixCategory::NORMALIZATION,
                PestSemanticFixability::AUTO_FIXABLE,
                PestSemanticSeverity::ERROR,
                PestSemanticSafetyLevel::SAFE,
                PestSemanticConfidence::HIGH,
                ['pest', 'execution', 'repeat', 'literal'],
                null,
                PestSemanticAutofixStrategy::LITERAL_NORMALIZATION,
                PestSemanticIssue::INTEROPERABILITY_VERSION,
                'execution',
                'repeat',
            ),
            self::BEFORE_ALL_IN_DESCRIBE => new PestSemanticIssue(
                self::BEFORE_ALL_IN_DESCRIBE,
                'beforeAll() cannot be used inside describe() blocks. Use beforeEach() instead.',
                [self::BEFORE_ALL_IN_DESCRIBE, 'pest.beforeAllInDescribe'],
                PestSemanticCategory::LIFECYCLE,
                PestSemanticFixCategory::NORMALIZATION,
                PestSemanticFixability::AUTO_FIXABLE,
                PestSemanticSeverity::ERROR,
                PestSemanticSafetyLevel::SAFE,
                PestSemanticConfidence::HIGH,
                ['pest', 'lifecycle', 'describe', 'beforeAll'],
                null,
                PestSemanticAutofixStrategy::DIRECT_REWRITE,
                PestSemanticIssue::INTEROPERABILITY_VERSION,
                'lifecycle',
                'describe-hooks',
            ),
            self::AFTER_ALL_IN_DESCRIBE => new PestSemanticIssue(
                self::AFTER_ALL_IN_DESCRIBE,
                'afterAll() cannot be used inside describe() blocks. Use afterEach() instead.',
                [self::AFTER_ALL_IN_DESCRIBE, 'pest.afterAllInDescribe'],
                PestSemanticCategory::LIFECYCLE,
                PestSemanticFixCategory::NORMALIZATION,
                PestSemanticFixability::AUTO_FIXABLE,
                PestSemanticSeverity::ERROR,
                PestSemanticSafetyLevel::SAFE,
                PestSemanticConfidence::HIGH,
                ['pest', 'lifecycle', 'describe', 'afterAll'],
                null,
                PestSemanticAutofixStrategy::DIRECT_REWRITE,
                PestSemanticIssue::INTEROPERABILITY_VERSION,
                'lifecycle',
                'describe-hooks',
            ),
            self::EMPTY_TEST_CLOSURE => new PestSemanticIssue(
                self::EMPTY_TEST_CLOSURE,
                'Test closure has an empty body and may indicate an unfinished test.',
                [self::EMPTY_TEST_CLOSURE, 'pest.emptyTestClosure'],
                PestSemanticCategory::TEST_DEFINITION,
                PestSemanticFixCategory::ASSISTANCE,
                PestSemanticFixability::INFORMATIONAL,
                PestSemanticSeverity::WARNING,
                PestSemanticSafetyLevel::REVIEW_REQUIRED,
                PestSemanticConfidence::HIGH,
                ['pest', 'test', 'closure', 'empty'],
                null,
                PestSemanticAutofixStrategy::NONE,
                PestSemanticIssue::INTEROPERABILITY_VERSION,
                'test-definition',
                'empty-closure',
            ),
            self::REDUNDANT_EXPECTATION => new PestSemanticIssue(
                self::REDUNDANT_EXPECTATION,
                'This expectation will always pass and may be redundant.',
                [self::REDUNDANT_EXPECTATION, 'pest.redundantExpectation'],
                PestSemanticCategory::EXPECTATION,
                PestSemanticFixCategory::CLEANUP,
                PestSemanticFixability::ASSISTED,
                PestSemanticSeverity::INFO,
                PestSemanticSafetyLevel::CONSERVATIVE,
                PestSemanticConfidence::MEDIUM,
                ['pest', 'expectation', 'literal', 'type'],
                'type',
                PestSemanticAutofixStrategy::CHAIN_CLEANUP,
                PestSemanticIssue::INTEROPERABILITY_VERSION,
                'expectation',
                'literal-type',
            ),
            self::IMPOSSIBLE_EXPECTATION => new PestSemanticIssue(
                self::IMPOSSIBLE_EXPECTATION,
                'This expectation can never pass for the inferred value type.',
                [self::IMPOSSIBLE_EXPECTATION, 'pest.impossibleExpectation'],
                PestSemanticCategory::EXPECTATION,
                PestSemanticFixCategory::ASSISTANCE,
                PestSemanticFixability::ASSISTED,
                PestSemanticSeverity::ERROR,
                PestSemanticSafetyLevel::REVIEW_REQUIRED,
                PestSemanticConfidence::MEDIUM,
                ['pest', 'expectation', 'literal', 'type'],
                'type',
                PestSemanticAutofixStrategy::REVIEW_HINT,
                PestSemanticIssue::INTEROPERABILITY_VERSION,
                'expectation',
                'literal-type',
            ),
        ];

        return self::$issues;
    }

    /**
     * @return array<string, PestSemanticIssue>
     */
    public static function all(): array
    {
        return self::issues();
    }

    public static function get(string $identifier): ?PestSemanticIssue
    {
        return self::issues()[$identifier] ?? null;
    }

    public static function staticTestClosure(): PestSemanticIssue
    {
        return self::issues()[self::STATIC_TEST_CLOSURE];
    }

    public static function invalidRepeatValue(): PestSemanticIssue
    {
        return self::issues()[self::INVALID_REPEAT_VALUE];
    }

    public static function beforeAllInDescribe(): PestSemanticIssue
    {
        return self::issues()[self::BEFORE_ALL_IN_DESCRIBE];
    }

    public static function afterAllInDescribe(): PestSemanticIssue
    {
        return self::issues()[self::AFTER_ALL_IN_DESCRIBE];
    }

    public static function emptyTestClosure(): PestSemanticIssue
    {
        return self::issues()[self::EMPTY_TEST_CLOSURE];
    }

    public static function redundantExpectation(): PestSemanticIssue
    {
        return self::issues()[self::REDUNDANT_EXPECTATION];
    }

    public static function impossibleExpectation(): PestSemanticIssue
    {
        return self::issues()[self::IMPOSSIBLE_EXPECTATION];
    }
}
