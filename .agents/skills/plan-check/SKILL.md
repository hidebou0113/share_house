---
name: plan-check
description: Create and independently review an evidence-based implementation plan before coding. Use for an Issue or proposed plan when code must remain unchanged.
---

# Plan check

Produce a plan that is ready for human approval without changing repository files.

## Preconditions

- Obtain the Issue or requirements and Acceptance Criteria. If they are incomplete, preserve the gap as an open question.
- Confirm the parent session is read-only. If it is not, stop and ask the human to switch permissions before continuing.

## Workflow

1. Inspect repository guidance, structure, existing behavior, relevant code and tests, dependencies, and CI commands.
2. Draft a candidate implementation plan tied to the Acceptance Criteria. Include affected areas, interfaces or data flow, compatibility, security considerations, and verification.
3. Spawn the `architect` agent with the requirements, candidate plan, and relevant scope. Ask it to independently inspect the repository and challenge the plan.
4. Incorporate only evidence-backed corrections. Keep unresolved product choices explicit for human decision.
5. Stop at the approval gate. Do not edit code, write a plan file, or begin implementation unless the user separately requests it.

## Output

Return:

- requirement and Acceptance Criteria summary
- existing behavior and affected paths or symbols
- proposed design and ordered implementation steps
- security, compatibility, migration, and rollback considerations when relevant
- test and verification plan
- architect review results and resulting corrections
- open questions and assumptions requiring human approval
