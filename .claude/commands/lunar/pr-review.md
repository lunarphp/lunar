---
description: Review the current Lunar branch (or a supplied base branch) against the project's review checklist.
argument-hint: "[base-branch]"
---

Invoke the `lunar-pr-review` skill to review the current branch against `$1` (default `1.x`).

Steps:
1. Call the `lunar-pr-review` skill via the Skill tool.
2. Pass the base branch as context: `${1:-1.x}`.
3. Render the report exactly as the skill's "Output Format" section describes — no extra commentary, no fixes applied.

Do not edit files. Do not push. Do not run `composer`/`npm`. Read-only review.
