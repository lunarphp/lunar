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

## Index

| #    | Title                       | Status      |
| ---- | --------------------------- | ----------- |
| 0001 | Upgrade package             | completed   |
| 0002 | Core namespace change       | completed   |
| 0003 | Flatten v1.x migrations     | completed   |
| 0004 | Filament v5 upgrade         | completed   |
| 0005 | Filament v5 schemas refactor | completed   |
| 0006 | Extract `lunarphp/filament` bridge package and reshape the install model | implemented |
| 0007 | Inline page-extension traits into base page classes | draft       |
| 0008 | Reusable Filament entity-selector components | implemented |
| 0009 | Filament-native verbs and discoverability (actions library + global search) | completed   |
| 0010 | Publishable admin resources (and Staff to core) | completed   |
| 0011 | Support `Model::preventLazyLoading()` | completed   |
| 0012 | Price data type / cast refactor | completed   |
| 0013 | `Base/` directory reorganisation | draft       |
| 0014 | Price calculator service | draft       |
| 0015 | PriceValue arithmetic | implemented |
| 0016 | Service-layer dependency injection | implemented |
| 0017 | Rename `compare_price` to `list_price` | completed   |
| 0018 | Dedicated `name` / `description` / `short_description` fields | completed   |
| 0019 | Attribute system redesign | completed   |
| 0020 | Remove GetCandy migration command | completed   |
| 0021 | State machines (and retiring soft-deletes) | completed   |
| 0022 | Order fulfilments, derived statuses & open/closed lifecycle | implemented |
| 0023 | Demo-data package | implemented |
| 0024 | Shipping carriers (tracking service registry) | implemented |
| 0025 | Order cancellation | implemented |
| 0026 | Bulk order operations | draft       |
| 0027 | Order print templates | draft       |
| 0028 | Line-item refunds | draft       |
| 0029 | Entry-point conventions: actions, model verbs, managers | implemented |
| 0030 | Fulfillable order lines | implemented |
| 0031 | Fulfilment methods: pluggable fulfilment flows | implemented |
| 0032 | Auto-close settled orders | implemented |
| 0033 | Multi-tenant homes for this branch's new config | implemented |
| 0034 | Wire and gate fulfilment notifications | implemented |
| 0035 | Interactive "Notify customer" order action | implemented |
| 0036 | Default professional customer notifications | draft       |
| 0037 | Move automatic notifications onto manifests | superseded by 0035 |
| 0038 | Inventory fundamentals | implemented |
| 0039 | Region | implemented |
| 0040 | Storefront context | implemented |
| 0041 | Retire model class substitution | implemented |
| 0042 | Model query builders (registerable scopes) | implemented |
| 0043 | Cache invalidation and event coverage | implemented |
| 0044 | Storefront cache tagging and dependency resolution | implemented |
