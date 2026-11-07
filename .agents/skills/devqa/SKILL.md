---
name: devqa
description: Perform independent acceptance QA against an Issue's Acceptance Criteria by running the repository's tests, lint, typecheck, build, and user scenarios. Use after implementation and validated review.
---

# Development QA

Produce acceptance evidence without fixing or extending the implementation.

## Preconditions

- Obtain the Issue and its Acceptance Criteria. Do not invent missing criteria.
- Use the independent `qa` agent; the implementation agent must not supply the final acceptance judgment.
- Start with least privilege. A human may select workspace-write when verified test or build commands need to create artifacts. Do not request broader access or install dependencies without approval.

## Workflow

1. Inspect repository guidance, manifests, task runners, and CI configuration to discover authoritative commands.
2. Build a matrix mapping every Acceptance Criterion to one or more observable checks.
3. Ask `qa` to run the relevant automated tests, lint, typecheck, and build. Record exact commands, exit results, and important output.
4. Exercise user scenarios against the runnable application when applicable. Record setup, actions, and observed behavior.
5. Do not edit source, tests, snapshots, or configuration to obtain a passing result. Report defects to the human and stop.

## Output

- Acceptance Criteria matrix with `pass`, `fail`, `blocked`, `not run`, or `not applicable`
- command log with observed results
- user-scenario evidence
- failures, blockers, and residual risks
- overall result: `pass` only when every applicable criterion is verified and all required available checks ran successfully; otherwise `fail` or `blocked`
- explicit human approval gate; do not merge, deploy, or perform production operations
