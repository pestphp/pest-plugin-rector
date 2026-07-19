<?php

declare(strict_types=1);

use Pest\Rector\Rules\ChainExpectCallsRector;
use Pest\Rector\Rules\ConvertAssertToExpectRector;
use Pest\Rector\Rules\ConvertBeforeAllInDescribeRector;
use Pest\Rector\Rules\ConvertExpectExceptionToThrowRector;
use Pest\Rector\Rules\EnsureTypeChecksFirstRector;
use Pest\Rector\Rules\FixInvalidRepeatValueRector;
use Pest\Rector\Rules\RemoveDebugExpectationsRector;
use Pest\Rector\Rules\RemoveOnlyRector;
use Pest\Rector\Rules\RemoveRedundantLiteralTypeExpectationRector;
use Pest\Rector\Rules\RemoveRedundantPestUsesRector;
use Pest\Rector\Rules\RemoveStaticTestClosureRector;
use Pest\Rector\Rules\SimplifyComparisonExpectationsRector;
use Pest\Rector\Rules\SimplifyExpectNotRector;
use Pest\Rector\Rules\SimplifyFilesystemMatchersRector;
use Pest\Rector\Rules\SimplifyToBeTruthyFalsyRector;
use Pest\Rector\Rules\SimplifyToLiteralBooleanRector;
use Pest\Rector\Rules\ToBeTrueNotFalseRector;
use Pest\Rector\Rules\UseEachModifierRector;
use Pest\Rector\Rules\UseInstanceOfMatcherRector;
use Pest\Rector\Rules\UseSequenceMatcherRector;
use Pest\Rector\Rules\UseStrictEqualityMatchersRector;
use Pest\Rector\Rules\UseToBeAlphaNumericRector;
use Pest\Rector\Rules\UseToBeAlphaRector;
use Pest\Rector\Rules\UseToBeBetweenRector;
use Pest\Rector\Rules\UseToBeDigitsRector;
use Pest\Rector\Rules\UseToBeDirectoryRector;
use Pest\Rector\Rules\UseToBeEmptyRector;
use Pest\Rector\Rules\UseToBeFileRector;
use Pest\Rector\Rules\UseToBeInfiniteRector;
use Pest\Rector\Rules\UseToBeInRector;
use Pest\Rector\Rules\UseToBeJsonRector;
use Pest\Rector\Rules\UseToBeListRector;
use Pest\Rector\Rules\UseToBeLowercaseRector;
use Pest\Rector\Rules\UseToBeNanRector;
use Pest\Rector\Rules\UseToBeUppercaseRector;
use Pest\Rector\Rules\UseToBeUrlRector;
use Pest\Rector\Rules\UseToBeUuidRector;
use Pest\Rector\Rules\UseToContainEqualRector;
use Pest\Rector\Rules\UseToContainOnlyInstancesOfRector;
use Pest\Rector\Rules\UseToContainRector;
use Pest\Rector\Rules\UseToEndWithRector;
use Pest\Rector\Rules\UseToEqualCanonicalizingRector;
use Pest\Rector\Rules\UseToEqualWithDeltaRector;
use Pest\Rector\Rules\UseToHaveCountRector;
use Pest\Rector\Rules\UseToHaveKeyRector;
use Pest\Rector\Rules\UseToHaveKeysRector;
use Pest\Rector\Rules\UseToHaveLengthRector;
use Pest\Rector\Rules\UseToHavePropertiesRector;
use Pest\Rector\Rules\UseToHavePropertyRector;
use Pest\Rector\Rules\UseToHaveSameSizeRector;
use Pest\Rector\Rules\UseToMatchArrayRector;
use Pest\Rector\Rules\UseToMatchObjectRector;
use Pest\Rector\Rules\UseToMatchRector;
use Pest\Rector\Rules\UseToStartWithRector;
use Pest\Rector\Rules\UseToThrowRector;
use Pest\Rector\Rules\UseTypeMatchersRector;
use Rector\Config\RectorConfig;

/**
 * Coding style improvements for Pest tests
 *
 * This set contains rules for:
 * - Better test readability and expressiveness
 * - Removing redundant code in tests
 * - Using more expressive Pest APIs
 * - Simplifying expect chains
 * - Using dedicated matchers instead of generic comparisons
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__.'/../config.php');

    // Assertion rewrites
    $rectorConfig->rule(ConvertAssertToExpectRector::class);

    // Iteration
    $rectorConfig->rule(UseEachModifierRector::class);
    $rectorConfig->rule(ConvertBeforeAllInDescribeRector::class);

    // Test cleanup
    $rectorConfig->rule(RemoveOnlyRector::class);
    $rectorConfig->rule(RemoveDebugExpectationsRector::class);
    $rectorConfig->rule(RemoveRedundantLiteralTypeExpectationRector::class);
    $rectorConfig->rule(RemoveRedundantPestUsesRector::class);
    $rectorConfig->rule(RemoveStaticTestClosureRector::class);
    $rectorConfig->rule(FixInvalidRepeatValueRector::class);

    // Boolean and negation simplification
    $rectorConfig->rule(SimplifyExpectNotRector::class);
    $rectorConfig->rule(ToBeTrueNotFalseRector::class);
    $rectorConfig->rule(SimplifyToLiteralBooleanRector::class);
    $rectorConfig->rule(SimplifyToBeTruthyFalsyRector::class);

    // Type matchers
    $rectorConfig->rule(UseTypeMatchersRector::class);
    $rectorConfig->rule(UseInstanceOfMatcherRector::class);
    $rectorConfig->rule(UseToBeDigitsRector::class);
    $rectorConfig->rule(UseToBeListRector::class);
    $rectorConfig->rule(UseToBeNanRector::class);
    $rectorConfig->rule(UseToBeInfiniteRector::class);

    // Comparison matchers
    $rectorConfig->rule(SimplifyComparisonExpectationsRector::class);
    $rectorConfig->rule(UseStrictEqualityMatchersRector::class);
    $rectorConfig->rule(UseToBeBetweenRector::class);
    $rectorConfig->rule(UseToBeInRector::class);
    $rectorConfig->rule(UseToBeEmptyRector::class);
    $rectorConfig->rule(UseToEqualCanonicalizingRector::class);
    $rectorConfig->rule(UseToEqualWithDeltaRector::class);

    // Array matchers
    $rectorConfig->rule(UseToContainRector::class);
    $rectorConfig->rule(UseToContainEqualRector::class);
    $rectorConfig->rule(UseToContainOnlyInstancesOfRector::class);
    $rectorConfig->rule(UseToHaveKeyRector::class);
    $rectorConfig->rule(UseToHaveKeysRector::class);
    $rectorConfig->rule(UseToHaveCountRector::class);
    $rectorConfig->rule(UseToHaveSameSizeRector::class);
    $rectorConfig->rule(UseToMatchArrayRector::class);
    $rectorConfig->rule(UseSequenceMatcherRector::class);

    // String matchers
    $rectorConfig->rule(UseToStartWithRector::class);
    $rectorConfig->rule(UseToEndWithRector::class);
    $rectorConfig->rule(UseToHaveLengthRector::class);
    $rectorConfig->rule(UseToMatchRector::class);
    $rectorConfig->rule(UseToBeJsonRector::class);
    $rectorConfig->rule(UseToBeUrlRector::class);
    $rectorConfig->rule(UseToBeUuidRector::class);
    $rectorConfig->rule(UseToBeUppercaseRector::class);
    $rectorConfig->rule(UseToBeLowercaseRector::class);
    $rectorConfig->rule(UseToBeAlphaRector::class);
    $rectorConfig->rule(UseToBeAlphaNumericRector::class);

    // File system matchers
    // UseToBeReadableWritableRector is intentionally not registered: it emits
    // toBeReadable()/toBeWritable(), which only exist as custom expectation
    // extensions. SimplifyFilesystemMatchersRector covers the cases where the
    // file/directory kind is known (is_file() && is_readable(), etc.).
    $rectorConfig->rule(UseToBeFileRector::class);
    $rectorConfig->rule(UseToBeDirectoryRector::class);
    $rectorConfig->rule(SimplifyFilesystemMatchersRector::class);

    // Object matchers
    $rectorConfig->rule(UseToHavePropertyRector::class);
    $rectorConfig->rule(UseToHavePropertiesRector::class);
    $rectorConfig->rule(UseToMatchObjectRector::class);

    // Exception matchers
    $rectorConfig->rule(UseToThrowRector::class);
    $rectorConfig->rule(ConvertExpectExceptionToThrowRector::class);

    // Expectation chaining (runs last, so the matchers introduced above can be chained)
    $rectorConfig->rule(ChainExpectCallsRector::class);      // Merges separate expect() calls
    $rectorConfig->rule(EnsureTypeChecksFirstRector::class); // Reorders type checks within chains
};
