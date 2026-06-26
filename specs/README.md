# Lunar v2 Specs

Design documents for the work tracked in `packages/lunar/TODO.md`.

## Conventions

- One file per item, named `NNNN-short-slug.md` (e.g. `0001-upgrade-package.md`).
- Numbers are allocated sequentially as specs are started; they do not imply priority or order of implementation.
- Use `0000-template.md` as the starting point for every new spec.
- A spec should land (reviewed and merged) before its implementation work begins.
- Keep specs in present tense, focused on the change being proposed, not the history of how we got here.
- Every spec ends with an `## Implementation plan` section listing the slices the work ships in, each independently reviewable. Keep its statuses current as slices land — it is the running record of what has shipped and what remains.

## Status

Each spec carries a `Status:` line in its frontmatter / header:

- `draft` — being written
- `proposed` — ready for review
- `accepted` — agreed, implementation can start
- `implemented` — work has shipped
- `superseded` — replaced by a later spec (link to it)
- `completed` — shipped and the spec moved to `specs/completed/`

## Index

| #    | Title                       | Status      |
| ---- | --------------------------- | ----------- |
| 0001 | Upgrade package             | completed   |
| 0002 | Core namespace change       | completed   |
| 0003 | Flatten v1.x migrations into a v2 baseline | completed   |
| 0004 | Filament v5 upgrade         | completed   |
| 0005 | Filament v5 schemas refactor | completed   |
| 0006 | Extract `lunarphp/filament` bridge package and reshape the install model | completed   |
| 0007 | Inline page-extension traits into base page classes | completed   |
| 0008 | Reusable Filament entity-selector components | completed   |
| 0009 | Filament-native verbs and discoverability (actions library + global search) | completed   |
| 0010 | Publishable admin resources (and Staff to core) | completed   |
| 0011 | Make Lunar safe under `Model::preventLazyLoading()` | completed   |
| 0012 | Price data type / cast refactor | completed   |
| 0013 | `Base/` directory reorganisation | completed   |
| 0014 | Price calculator service | completed   |
| 0015 | PriceValue arithmetic | completed   |
| 0016 | Service-layer dependency injection | completed   |
| 0017 | Rename `compare_price` to `list_price` | completed   |
| 0018 | Dedicated `name` / `description` / `short_description` fields | completed   |
| 0019 | Attribute system redesign | completed   |
| 0020 | Remove GetCandy migration command | completed   |
| 0021 | State machines (and retiring soft-deletes) | completed   |
| 0022 | Order fulfilments, derived statuses & open/closed lifecycle | completed   |
| 0023 | Demo-data package | accepted    |
| 0024 | Shipping carriers (tracking service registry) | completed   |
| 0025 | Order cancellation | completed   |
| 0026 | Bulk order operations | draft       |
| 0027 | Order print templates | draft       |
| 0028 | Line-item refunds | draft       |
| 0029 | Entry-point conventions: actions, model verbs, managers | completed   |
| 0030 | Fulfillable order lines: decouple fulfilment from the line `type` string | completed   |
| 0031 | Fulfilment methods: pluggable fulfilment flows | completed   |
| 0032 | Auto-close settled orders | completed   |
| 0033 | Multi-tenant homes for this branch's new config | completed   |
| 0034 | Wire and gate fulfilment notifications | completed   |
| 0035 | Interactive "Notify customer" order action | proposed    |
| 0036 | Default professional customer notifications for the order lifecycle | draft       |
| 0037 | Move automatic notifications onto manifests (split by payload) | superseded  |
| 0038 | Inventory fundamentals | completed   |
