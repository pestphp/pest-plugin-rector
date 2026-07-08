<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\ConvertBeforeAllInDescribeRector;
use RectorPest\Rules\FixInvalidRepeatValueRector;
use RectorPest\Rules\RemoveDebugExpectationsRector;
use RectorPest\Rules\RemoveOnlyRector;
use RectorPest\Rules\RemoveRedundantLiteralTypeExpectationRector;
use RectorPest\Rules\RemoveRedundantPestUsesRector;
use RectorPest\Rules\RemoveStaticTestClosureRector;
use RectorPest\Rules\SimplifyComparisonExpectationsRector;
use RectorPest\Rules\SimplifyExpectNotRector;
use RectorPest\Rules\SimplifyFilesystemMatchersRector;
use RectorPest\Rules\SimplifyToBeTruthyFalsyRector;
use RectorPest\Rules\SimplifyToLiteralBooleanRector;
use RectorPest\Rules\ToBeTrueNotFalseRector;
use RectorPest\Rules\UseEachModifierRector;
use RectorPest\Rules\UseInstanceOfMatcherRector;
use RectorPest\Rules\UseStrictEqualityMatchersRector;
use RectorPest\Rules\UseToBeAlphaNumericRector;
use RectorPest\Rules\UseToBeAlphaRector;
use RectorPest\Rules\UseToBeBetweenRector;
use RectorPest\Rules\UseToBeDigitsRector;
use RectorPest\Rules\UseToBeDirectoryRector;
use RectorPest\Rules\UseToBeEmptyRector;
use RectorPest\Rules\UseToBeFileRector;
use RectorPest\Rules\UseToBeInfiniteRector;
use RectorPest\Rules\UseToBeInRector;
use RectorPest\Rules\UseToBeJsonRector;
use RectorPest\Rules\UseToBeListRector;
use RectorPest\Rules\UseToBeLowercaseRector;
use RectorPest\Rules\UseToBeNanRector;
use RectorPest\Rules\UseToBeReadableWritableRector;
use RectorPest\Rules\UseToBeUppercaseRector;
use RectorPest\Rules\UseToBeUrlRector;
use RectorPest\Rules\UseToBeUuidRector;
use RectorPest\Rules\UseToContainEqualRector;
use RectorPest\Rules\UseToContainOnlyInstancesOfRector;
use RectorPest\Rules\UseToContainRector;
use RectorPest\Rules\UseToEndWithRector;
use RectorPest\Rules\UseToEqualCanonicalizingRector;
use RectorPest\Rules\UseToEqualWithDeltaRector;
use RectorPest\Rules\UseToHaveCountRector;
use RectorPest\Rules\UseToHaveKeyRector;
use RectorPest\Rules\UseToHaveKeysRector;
use RectorPest\Rules\UseToHaveLengthRector;
use RectorPest\Rules\UseToHavePropertiesRector;
use RectorPest\Rules\UseToHavePropertyRector;
use RectorPest\Rules\UseToHaveSameSizeRector;
use RectorPest\Rules\UseToMatchArrayRector;
use RectorPest\Rules\UseToMatchObjectRector;
use RectorPest\Rules\UseToMatchRector;
use RectorPest\Rules\UseToStartWithRector;
use RectorPest\Rules\UseToThrowRector;
use RectorPest\Rules\UseTypeMatchersRector;

/**
 * Code quality improvements for Pest tests
 *
 * This set contains rules for:
 * - Better test readability and expressiveness
 * - Removing redundant code in tests
 * - Using more expressive Pest APIs
 * - Simplifying expect chains
 * - Using dedicated matchers instead of generic comparisons
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

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
    $rectorConfig->rule(UseToBeFileRector::class);
    $rectorConfig->rule(UseToBeDirectoryRector::class);
    $rectorConfig->rule(UseToBeReadableWritableRector::class);
    $rectorConfig->rule(SimplifyFilesystemMatchersRector::class);

    // Object matchers
    $rectorConfig->rule(UseToHavePropertyRector::class);
    $rectorConfig->rule(UseToHavePropertiesRector::class);
    $rectorConfig->rule(UseToMatchObjectRector::class);

    // Exception matchers
    $rectorConfig->rule(UseToThrowRector::class);
};
