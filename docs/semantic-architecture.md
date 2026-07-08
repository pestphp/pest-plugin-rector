# Semantic Architecture

Rector Pest now has two layers:

- syntax-oriented Rector rules that rewrite obvious Pest patterns
- semantic remediation infrastructure for diagnostics that need deterministic, behavior-safe reasoning

The semantic layer is intentionally analyzer-agnostic. Rector Pest does not need PestStan at runtime to classify issues, resolve identifiers, or map safe fixes for package consumers. This repository still uses PestStan as a development-only PHPStan extension, which is separate from the runtime interoperability contract exposed by Rector Pest itself.

## Design Goals

- keep canonical issue identifiers stable across analyzers and IDE integrations
- centralize semantic metadata in one registry instead of duplicating it across rules
- let analyzers return local facts instead of hard-coding remediation decisions
- keep auto-fixes limited to deterministic, behavior-preserving transformations
- leave ambiguous or behavior-changing cases modeled but not automatically rewritten

## Canonical Contracts

The canonical registry lives in `src/Registry/PestSemanticIssues.php` and defines:

- the stable issue identifier
- legacy diagnostic aliases for interoperability
- severity, fixability, safety, confidence, and tags
- matcher category, fix strategy, issue family, semantic group, and interop version

Every semantic rule extends `AbstractSemanticPestRector`, which exposes the registry metadata without re-encoding it inside each Rector rule.

## Semantic Flow

1. A local analyzer inspects a node and returns deterministic facts.
2. A semantic Rector decides whether those facts are safe enough to rewrite.
3. The interop layer maps external diagnostic identifiers to canonical issues.
4. `SemanticIssueMapper` resolves the canonical issue to one or more safe Rector rules.

That flow keeps analysis and remediation separate. An analyzer can say that a matcher is redundant, impossible, or unsupported without directly deciding how a fix should be applied.

## Current Building Blocks

### Deterministic analyzers

- `PestChainAnalyzer` provides lightweight chain-shape helpers.
- `HookSemanticAnalyzer` finds invalid hook placement inside `describe()` blocks.
- `SemanticExpectationAnalyzer` classifies deterministic literal type checks using factual output such as matcher category, literal category, negation, and match result.

### Canonical issue and fix mapping

- `PestSemanticIssue` is the immutable metadata contract.
- `PestDiagnosticResolver` canonicalizes external diagnostic identifiers.
- `SemanticFixCandidate` is the DTO for a potential safe fix.
- `SemanticIssueMapper` maps canonical issues to semantic Rector rules.

### Safe semantic rules

Current auto-fixes intentionally stay narrow:

- remove static Pest callbacks only when instance binding is actually required
- replace invalid `beforeAll()` and `afterAll()` inside `describe()` with per-test hooks
- normalize invalid literal `repeat()` counts to `1`
- remove redundant literal type expectations only when the value is deterministic and the chain stays on the same expectation subject

Modeled-but-not-auto-fixed issues remain part of the registry so future tooling can surface review hints without forcing unsafe rewrites.

## Safety Boundaries

The semantic layer avoids becoming a second full static analyzer.

- analyzers only use local AST facts that are deterministic from the source node
- unsupported literals or transformed expectation subjects return no semantic result
- branching and subject-transforming chain methods such as `and()` and `json()` block redundant-cleanup rewrites
- nested Pest callbacks are analyzed independently, so an outer callback is not rewritten just because an inner Pest hook needs `$this`
- nested non-Pest closure trees still count when they require the surrounding Pest callback to stay instance-bound

## Interoperability Guarantees

The package is ready to consume diagnostics from external tools through canonical identifiers instead of direct runtime coupling.

- canonical identifiers stay primary
- legacy aliases remain supported through the resolver
- DTOs expose serializable metadata for future IDE, SARIF, or analyzer integrations
- issue-to-fix mapping is explicit and test-covered

This makes it possible to share diagnostics across Rector Pest, PestStan, editor tooling, and future ecosystem packages without duplicating transformation logic.
