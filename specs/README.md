# Lunar v2 Specs

Design documents for the work tracked in `packages/lunar/TODO.md`.

## Conventions

- One file per item, named `NNNN-short-slug.md` (e.g. `0001-upgrade-package.md`).
- Numbers are allocated sequentially as specs are started; they do not imply priority or order of implementation.
- Use `0000-template.md` as the starting point for every new spec.
- A spec should land (reviewed and merged) before its implementation work begins.
- Keep specs in present tense, focused on the change being proposed, not the history of how we got here.

## Status

Each spec carries a `Status:` line in its frontmatter / header:

- `draft` — being written
- `proposed` — ready for review
- `accepted` — agreed, implementation can start
- `implemented` — work has shipped
- `superseded` — replaced by a later spec (link to it)

## Index

| #    | Title                 | Status |
| ---- | --------------------- | ------ |
| 0001 | Upgrade package       | draft  |
| 0002 | Core namespace change | draft  |
