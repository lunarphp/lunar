# Lunar v2 Specs

Design documents for the work tracked in `packages/lunar/TODO.md`.

## Conventions

- One file per item, named after a short kebab-case slug: `cart-totals-caching.md`. The slug is the spec's name: refer to it as `spec cart-totals-caching` in code, commits and PRs, and as `[[cart-totals-caching]]` between specs.
- Specs have no numbers, so parallel PRs never compete for the next one. Choose a slug that no existing spec, in `specs/` or `specs/completed/`, already uses.
- Older specs keep their numbered names (`0093-registrable-calculation-steps.md`), and references to them stay as they are.
- Refer to a spec by its name, never by its path, so moving it to `completed/` breaks nothing.
- Use `template.md` as the starting point for every new spec. The `Created:` date records when it was started.
- A spec should land (reviewed and merged) before its implementation work begins.
- Keep specs in present tense, focused on the change being proposed, not the history of how we got here.
- Every spec ends with an `## Implementation plan` section listing the slices the work ships in, each independently reviewable. Keep its statuses current as slices land — it is the running record of what has shipped and what remains.
- Move a spec to `specs/completed/` once its implementation has shipped.

## Status

Each spec carries a `Status:` line in its frontmatter / header:

- `draft` — being written
- `proposed` — ready for review
- `accepted` — agreed, implementation can start
- `implemented` — work has shipped
- `superseded` — replaced by a later spec (link to it)
- `declined` — no longer being considered

The header is the only record of a spec's status; there is no index to keep in step. To list them:

```bash
grep -H "^- Status:" specs/*.md specs/completed/*.md
```
