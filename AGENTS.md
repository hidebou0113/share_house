# Repository guidance

## Source of truth

- Treat the Issue or stated requirements and its Acceptance Criteria as the source of truth.
- Do not invent product behavior. Surface material ambiguity and wait for a human decision.
- Keep changes inside the approved Issue scope. Do not include opportunistic refactors.

## Development lifecycle

Use this sequence for application changes:

1. Run `plan-check` in a read-only session. Obtain human approval of the resulting implementation plan.
2. Switch to workspace-write only for the approved implementation. The implementing agent must not be the final reviewer or acceptance tester.
3. Switch back to read-only and run `pr-check`. Correctness and security review must be performed by separate agents, and every candidate finding must be rechecked by `finding-validator`.
4. Run `devqa` with an independent `qa` agent. Grant workspace-write only if test or build artifacts require it.
5. Present the validated review and QA evidence for human approval. Do not merge or release automatically.

## Review and verification rules

- Reviewer roles are non-editing roles. They may inspect evidence but must not create, modify, or delete files.
- Do not report formatter or other mechanically enforceable style findings; leave them to the repository's automated checks.
- Report only findings that are reproducible from the stated change scope and supported by concrete evidence.
- Never claim a test, lint, typecheck, build, or scenario passed unless it was actually run and its successful result was observed.
- Record checks that could not run as `blocked` or `not run`, with the reason.
- Do not commit, push, merge, publish, deploy, or perform production operations unless a human explicitly requests that separate action.
- This repository does not define a `/ship`-style autonomous flow.

## Permissions

- Project sessions start read-only. Use `/permissions` to select workspace-write only for implementation or QA commands that need to write artifacts.
- Custom agents inherit the parent session's sandbox. Before using `architect`, `reviewer`, `security-reviewer`, or `finding-validator`, confirm the parent session is read-only.
- Review agents must not request elevated permissions. If evidence cannot be gathered read-only, report the limitation instead.

## Commands

- Discover authoritative commands from repository documentation, manifests, task runners, and CI configuration.
- Prefer the narrowest relevant verification first, then run the full required checks when feasible.
- Do not install dependencies or change tool configuration merely to make a check pass without human approval.
