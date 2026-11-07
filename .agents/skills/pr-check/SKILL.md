---
name: pr-check
description: Review a precisely identified change set with independent correctness and security agents, then validate every candidate finding to remove false positives. Use after implementation and before acceptance QA.
---

# PR check

Return only validated, actionable findings. Do not modify the repository.

## Preconditions

- Confirm the parent session is read-only. Custom agents inherit this permission boundary.
- Identify and state the exact review target before reviewing: user-supplied base/head, PR metadata, or the current working-tree changes.
- Inspect status and diffs, including relevant untracked files. If multiple review ranges are plausible, ask the human instead of guessing.
- Gather the Issue, Acceptance Criteria, and approved plan when available. Missing context must be stated.

## Independent review

1. Give the same declared change scope and requirements to `reviewer` and `security-reviewer` in separate agent tasks. Run them concurrently when possible, and do not share one reviewer's conclusions with the other.
2. The `reviewer` checks correctness, regressions, and meaningful test gaps. The `security-reviewer` checks security impact and trust boundaries.
3. Exclude formatting, lint, naming, and other mechanically enforceable style comments from AI review.

## Finding validation

1. Collect candidate findings without treating them as true.
2. Give each claim and its cited evidence to a separate `finding-validator` task. Ask it to reproduce the claim against the exact change scope and surrounding code.
3. Remove rejected findings and duplicates. Do not present `needs-more-information` claims as confirmed defects; list them separately as questions only when they materially block approval.

## Output

- State the reviewed base/head or working-tree scope.
- List confirmed findings first, ordered by impact. For each, include location, failure or attack path, impact, validation evidence, and confidence.
- Then list blocking questions, validation limitations, and checks not run.
- If nothing is confirmed, say so directly and mention residual validation limits. Do not claim the change is globally defect-free.
