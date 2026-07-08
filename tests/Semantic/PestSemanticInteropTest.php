<?php

declare(strict_types=1);

use RectorPest\Interop\PestDiagnosticResolver;
use RectorPest\Interop\SemanticIssueMapper;
use RectorPest\Registry\PestSemanticIssues;
use RectorPest\Rules\ConvertBeforeAllInDescribeRector;
use RectorPest\Rules\FixInvalidRepeatValueRector;
use RectorPest\Rules\RemoveRedundantLiteralTypeExpectationRector;
use RectorPest\ValueObject\PestSemanticAutofixStrategy;
use RectorPest\ValueObject\PestSemanticFixability;
use RectorPest\ValueObject\PestSemanticIssue;
use RectorPest\ValueObject\PestSemanticSafetyLevel;
use RectorPest\ValueObject\PestSemanticSeverity;

it('exposes canonical metadata for invalid repeat values', function (): void {
    $issue = PestSemanticIssues::invalidRepeatValue();
    $sameIssue = PestSemanticIssues::get(PestSemanticIssues::INVALID_REPEAT_VALUE);

    expect($sameIssue)->toBe($issue);
    expect($issue->identifier)->toBe(PestSemanticIssues::INVALID_REPEAT_VALUE);
    expect($issue->allDiagnosticIdentifiers())->toBe([
        PestSemanticIssues::INVALID_REPEAT_VALUE,
        'pest.repeatInvalidValue',
    ]);
    expect($issue->defaultMessage)->toContain('greater than 0');
    expect($issue->severity)->toBe(PestSemanticSeverity::ERROR);
    expect($issue->fixability)->toBe(PestSemanticFixability::AUTO_FIXABLE);
    expect($issue->safetyLevel)->toBe(PestSemanticSafetyLevel::SAFE);
    expect($issue->autofixStrategy)->toBe(PestSemanticAutofixStrategy::LITERAL_NORMALIZATION);
    expect($issue->issueFamily)->toBe('execution');
    expect($issue->semanticGroup)->toBe('repeat');
    expect($issue->interoperabilityVersion)->toBe('1.0.0');
    expect($issue->isAutoFixable())->toBeTrue();
    expect(array_keys($issue->toArray()))->toBe([
        'identifier',
        'defaultMessage',
        'diagnosticIdentifiers',
        'category',
        'fixCategory',
        'fixability',
        'severity',
        'safetyLevel',
        'confidence',
        'tags',
        'matcherCategory',
        'autofixStrategy',
        'interoperabilityVersion',
        'issueFamily',
        'semanticGroup',
    ]);
});

it('resolves machine-readable diagnostics to canonical semantic issues', function (): void {
    $resolver = new PestDiagnosticResolver();

    $issue = $resolver->resolve('pest.afterAllInDescribe');
    $canonicalIssue = $resolver->resolve(PestSemanticIssues::AFTER_ALL_IN_DESCRIBE);

    expect($issue)->not->toBeNull();
    expect($canonicalIssue)->toBe($issue);
    expect($issue?->identifier)->toBe(PestSemanticIssues::AFTER_ALL_IN_DESCRIBE);
    expect($resolver->canonicalize('pest.afterAllInDescribe'))->toBe(PestSemanticIssues::AFTER_ALL_IN_DESCRIBE);
    expect($resolver->supports(PestSemanticIssues::AFTER_ALL_IN_DESCRIBE))->toBeTrue();
    expect($resolver->supportedDiagnosticIdentifiers())->toBe([
        PestSemanticIssues::STATIC_TEST_CLOSURE,
        'pest.staticTestClosure',
        PestSemanticIssues::INVALID_REPEAT_VALUE,
        'pest.repeatInvalidValue',
        PestSemanticIssues::BEFORE_ALL_IN_DESCRIBE,
        'pest.beforeAllInDescribe',
        PestSemanticIssues::AFTER_ALL_IN_DESCRIBE,
        'pest.afterAllInDescribe',
        PestSemanticIssues::EMPTY_TEST_CLOSURE,
        'pest.emptyTestClosure',
        PestSemanticIssues::REDUNDANT_EXPECTATION,
        'pest.redundantExpectation',
        PestSemanticIssues::IMPOSSIBLE_EXPECTATION,
        'pest.impossibleExpectation',
    ]);
});

it('maps diagnostics to semantic fix candidates', function (): void {
    $mapper = new SemanticIssueMapper();

    $describeCandidates = $mapper->resolveCandidatesForDiagnostic(PestSemanticIssues::BEFORE_ALL_IN_DESCRIBE);
    $repeatCandidates = $mapper->resolveCandidatesForDiagnostic(PestSemanticIssues::INVALID_REPEAT_VALUE);
    $redundantCandidates = $mapper->resolveCandidatesForDiagnostic(PestSemanticIssues::REDUNDANT_EXPECTATION);
    $emptyCandidates = $mapper->resolveCandidatesForDiagnostic(PestSemanticIssues::EMPTY_TEST_CLOSURE);
    $impossibleCandidates = $mapper->resolveCandidatesForDiagnostic(PestSemanticIssues::IMPOSSIBLE_EXPECTATION);

    expect($describeCandidates)->toHaveCount(1);
    expect($describeCandidates[0]->rectorClass)->toBe(ConvertBeforeAllInDescribeRector::class);
    expect($describeCandidates[0]->matchedDiagnosticIdentifier)->toBe(PestSemanticIssues::BEFORE_ALL_IN_DESCRIBE);

    expect($repeatCandidates)->toHaveCount(1);
    expect($repeatCandidates[0]->rectorClass)->toBe(FixInvalidRepeatValueRector::class);
    expect($repeatCandidates[0]->issue->identifier)->toBe(PestSemanticIssues::INVALID_REPEAT_VALUE);
    expect($repeatCandidates[0]->toArray())->toMatchArray([
        'issueIdentifier' => PestSemanticIssues::INVALID_REPEAT_VALUE,
        'matchedDiagnosticIdentifier' => PestSemanticIssues::INVALID_REPEAT_VALUE,
        'autofixStrategy' => PestSemanticAutofixStrategy::LITERAL_NORMALIZATION,
        'interoperabilityVersion' => '1.0.0',
    ]);
    expect(array_keys($repeatCandidates[0]->toArray()))->toBe([
        'issueIdentifier',
        'matchedDiagnosticIdentifier',
        'rectorClass',
        'fixability',
        'safetyLevel',
        'autofixStrategy',
        'interoperabilityVersion',
        'issue',
    ]);

    expect($redundantCandidates)->toHaveCount(1);
    expect($redundantCandidates[0]->rectorClass)->toBe(RemoveRedundantLiteralTypeExpectationRector::class);
    expect($redundantCandidates[0]->issue->fixability)->toBe(PestSemanticFixability::ASSISTED);
    expect($redundantCandidates[0]->isAutoFixable())->toBeFalse();

    expect($emptyCandidates)->toBe([]);
    expect($impossibleCandidates)->toBe([]);
});

it('keeps empty test closures as informational diagnostics only', function (): void {
    $issue = PestSemanticIssues::emptyTestClosure();

    expect($issue->fixability)->toBe(PestSemanticFixability::INFORMATIONAL);
    expect($issue->safetyLevel)->toBe(PestSemanticSafetyLevel::REVIEW_REQUIRED);
    expect($issue->isAutoFixable())->toBeFalse();
    expect($issue->toArray())->toMatchArray([
        'identifier' => PestSemanticIssues::EMPTY_TEST_CLOSURE,
        'autofixStrategy' => PestSemanticAutofixStrategy::NONE,
        'issueFamily' => 'test-definition',
        'semanticGroup' => 'empty-closure',
    ]);
});

it('keeps resolver and mapper stable across canonical and legacy identifiers', function (): void {
    $resolver = new PestDiagnosticResolver();
    $mapper = new SemanticIssueMapper();

    $resolvedIssues = $resolver->resolveAll([
        PestSemanticIssues::STATIC_TEST_CLOSURE,
        'pest.staticTestClosure',
        PestSemanticIssues::REDUNDANT_EXPECTATION,
        'pest.redundantExpectation',
    ]);

    expect(array_map(static fn (PestSemanticIssue $issue): string => $issue->identifier, $resolvedIssues))
        ->toBe([
            PestSemanticIssues::STATIC_TEST_CLOSURE,
            PestSemanticIssues::REDUNDANT_EXPECTATION,
        ]);

    expect($mapper->supportsDiagnostic('pest.staticTestClosure'))->toBeTrue();
    expect($mapper->supportsIssue(PestSemanticIssues::REDUNDANT_EXPECTATION))->toBeTrue();
    expect($resolver->supportedDiagnosticIdentifiers())->toContain('pest.afterAllInDescribe');
    expect($resolver->supportedDiagnosticIdentifiers())->toContain(PestSemanticIssues::AFTER_ALL_IN_DESCRIBE);
});
