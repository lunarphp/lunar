# Agent instructions in AGENTS.md

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-11-10
- TODO item: Agent instructions in `AGENTS.md` — Codex and other agents read the same instructions as Claude Code (spec agents-md)

## Problem

The monorepo's agent instructions live in three `CLAUDE.md` files: the root (12 KB), `packages/filament/CLAUDE.md` (11 KB) and `packages/panel/CLAUDE.md` (5 KB). Only Claude Code reads them.

Lunar is open source, and contributors also use Codex, Cursor, Copilot and other agents. Most of those read `AGENTS.md`. Codex reads `AGENTS.md` and `AGENTS.override.md` only, so a Codex contributor gets none of the translation, contract-surface, service-layer, test-suite or PHPStan rules unless they configure a fallback filename themselves.

Claude Code v2.1.277 and later reads `AGENTS.md`, but only as a fallback: it reads `AGENTS.md` when there is no `CLAUDE.md`, `.claude/CLAUDE.md` or `CLAUDE.local.md` in the working directory or any directory above it. Renaming the files is therefore not enough:

- **A host app with a `CLAUDE.md` blocks the fallback.** Lunar is developed inside a host Laravel app, and a Boost-managed host app usually has a `CLAUDE.md`. Claude Code would load the host app's file and skip Lunar's `AGENTS.md`.
- **Some sessions can't read `AGENTS.md`.** That includes sessions where the built-in plugin is disabled, the first session after an upgrade from v2.1.276 or earlier, and, before v2.1.281, Bedrock, Vertex and telemetry-off sessions.
- **The `claude-code-action` workflows** (`pr-review.yml`, `pr-triage.yml`, `issue-triage.yml`) run a pinned action whose Claude Code version isn't checked against these limits.

Codex also discovers nested files differently from Claude Code. It walks from the project root down to the directory it was launched in, and loads at most one file per directory. Launched from the monorepo root, it never sees `packages/panel/` or `packages/filament/` instructions.

## Proposal

`AGENTS.md` holds the instructions. Each `CLAUDE.md` becomes a one-line import of the `AGENTS.md` next to it.

### Files

| Path | Holds |
| --- | --- |
| `AGENTS.md` | The current root `CLAUDE.md` content |
| `CLAUDE.md` | `@AGENTS.md` |
| `packages/panel/AGENTS.md` | The current `packages/panel/CLAUDE.md` content |
| `packages/panel/CLAUDE.md` | `@AGENTS.md` |
| `packages/filament/AGENTS.md` | The current `packages/filament/CLAUDE.md` content |
| `packages/filament/CLAUDE.md` | `@AGENTS.md` |

The `CLAUDE.md` files are plain files, not symlinks. On Windows, Git checks a committed symlink out as a text file unless `core.symlinks` is enabled, and Claude Code's Edit and Write tools refuse to write through a symlink.

The import never loads `AGENTS.md` twice, whichever **Project instructions** setting a contributor uses. `InstructionsLoaded` hooks fire for it as they do for any `CLAUDE.md`.

### Content changes

The move keeps the content as it is, except where it assumes Claude Code:

- **Nested files.** The root `AGENTS.md` gains a section that tells agents to read `packages/panel/AGENTS.md` before working in `packages/panel/`, and `packages/filament/AGENTS.md` before working in `packages/filament/`. Claude Code still loads the nested files on demand. The section is for Codex launched from the root.
- **Reviewing changes.** The root file names the review checklist by path, `.claude/skills/lunar-pr-review/SKILL.md`, as the `pr-review.yml` workflow already does. `/lunar:pr-review` stays as the Claude Code shortcut.
- **Self-references.** The root file's mentions of "the project-wide `CLAUDE.md`" in the host app section stay, because they describe the host app's file, not this one.

### References

- `.claude/skills/lunar-pr-review/SKILL.md` and `references/checklist.md` cite rules "per `CLAUDE.md`". They now cite `AGENTS.md`.
- The `Documentation` and `Spec-driven development` sections of the root file, and `specs/README.md`, need no change.
- Completed specs in `specs/completed/` that mention `CLAUDE.md` stay as they are. They record what shipped at the time.

### What stays

- **`.claude/`** keeps the skill, command, review rubric and triage rules. The three workflows read those files by path. Moving them to a tool-neutral directory is a separate change, if it's ever wanted.
- **The host app's `CLAUDE.md`** is outside this repository and out of scope.

### Size

The three files total about 28.6 KB. Codex stops adding instruction files once the combined size reaches `project_doc_max_bytes`, which defaults to 32 KiB. Codex launched from `packages/filament/` loads the root and filament files, about 23.5 KB. A note at the top of the root `AGENTS.md` says to keep it under 16 KB, so the root plus either package file stays within the default.

## Alternatives considered

- **Delete `CLAUDE.md` and rely on Claude Code's fallback.** Rejected: a host app `CLAUDE.md` above the monorepo stops Claude Code reading Lunar's instructions, which is the common setup for Lunar contributors.
- **Symlink `CLAUDE.md` to `AGENTS.md`.** Rejected: breaks on Windows clones without `core.symlinks`, and agents that edit `CLAUDE.md` are refused.
- **Keep `CLAUDE.md` and add an `AGENTS.md` that says "read `CLAUDE.md`".** Rejected: Codex only sees the content if the model decides to open the file, and the instructions keep their Claude-specific home.
- **Ask Codex users to set `project_doc_fallback_filenames = ["CLAUDE.md"]`.** Rejected: every contributor has to discover and configure it, and other agents need their own settings.
- **Do nothing.** Contributors using other agents miss the rules the review workflow enforces, and their PRs fail review on rules they never saw.

## Migration impact

- Database migrations required: none.
- Breaking changes to the public contract surface: none. Split packages ship `AGENTS.md` and a one-line `CLAUDE.md` where they shipped `CLAUDE.md` before.
- Upgrade path for v1.x consumers: none. `1.x` has no `CLAUDE.md`.
- Translation / locale impact: none.
- Filament / admin impact: none.
- Open branches: most open branches carry the three `CLAUDE.md` files. A branch that edits one conflicts when it rebases, because the content moves to `AGENTS.md`. Its edit is reapplied to `AGENTS.md` during the rebase.

## Open questions

- **Root section on the host app.** It names Glenn's local path and Herd URL. It's accurate for the maintainers but not for other contributors. Owner: Glenn, before `accepted`. It can stay as it is for this change and be generalised later.

## References

- [Claude Code docs: AGENTS.md](https://code.claude.com/docs/en/memory#agents-md)
- [Codex docs: AGENTS.md](https://learn.chatgpt.com/docs/agent-configuration/agents-md)
- [agents.md](https://agents.md)
- `.github/workflows/pr-review.yml`, `pr-triage.yml`, `issue-triage.yml`

## Implementation plan

- [ ] Slice 1 — Move the three `CLAUDE.md` files to `AGENTS.md` with `git mv`, add the one-line `CLAUDE.md` imports, make the content changes above, update the `lunar-pr-review` skill references, and check in Claude Code (`/memory` lists each `AGENTS.md`) and Codex (launched from the root and from `packages/panel/`) that each loads the right files.
