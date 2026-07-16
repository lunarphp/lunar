# Lunar Panel (`lunarphp/panel`)

The Inertia + Vue admin panel. This file documents conventions specific to
building inside the panel; the monorepo and project-wide rules still apply.

## Building a panel page

Every content page must expose the standard extension seams so add-ons can
inject into it without the page opting in. Do not hand-roll a bare page header.

- **Header:** render the page title through `<PageHeader>` (top-level pages) or
  pass `title` to `<SettingsShell>` (settings pages). Both render the shared
  page-action ellipsis (`<PageActions>`), so an add-on's `PageAction` always has
  a home. Put page-specific primary buttons in `<PageHeader>`'s `#actions` slot.
- **Slot zones:** wrap the main body with `<PageZone region="main" position="before" />`
  and `<PageZone region="main" position="after" />`. `PageZone` scopes the zone
  to the current page id, producing the standard name `{pageId}:{region}[:position]`
  (e.g. `customers.edit:main:after`). Add further named zones with the same
  component where a page has meaningful injection points.
- **Actions, not hardcoded buttons:** row and header actions are registered as
  `TableAction` / `PageAction` classes on a `Section` (see `SalesSection`,
  `ChannelsSection`), not written as bespoke buttons in the Vue. This is what
  makes them reorderable (via `Position`) and injectable by add-ons.

`tests/panel/Unit/PageScaffoldTest.php` enforces that every content page (auth
and account pages excepted) goes through `PageHeader` or `SettingsShell`.

First-party pages import these components directly and wrap their own
`<PanelLayout>`. Add-on pages can't import panel source, so the panel exposes a
public page-building surface to them: `app.ts` publishes the components on
`window.LunarPanelUI`, and `@lunarphp/panel-vite-plugin` externalises the
`@lunarphp/panel` import to that global. **`resources/js/ui.ts` is the source of
truth for what add-ons can import** — the barrel there lists the whole set (layout,
DataTable, FilterDropdown, KpiCard, form inputs, overlays, `Button`, `Icon`, …).
Internal machinery (`NavBody`, `PanelSlot`, `RowActions`, `PageActions`, `Section`, …)
is deliberately not exported. The `PanelLayout` shell is applied to add-on pages
automatically (the resolver sets it as their persistent layout via the `default`
entry in the layout registry), so an add-on page only builds its own `PageHeader` +
content, never the sidebar.

Editing the public surface is normally just `ui.ts`: the runtime global
(`window.LunarPanelUI`) is spread from the ui.ts namespace in app.ts, its type is
`typeof import('../ui')`, and the `@lunarphp/panel` package's `dist/ui.d.ts` types
are generated from ui.ts by `npm run build:types` (vue-tsc, via
`tsconfig.build-types.json`; run automatically as part of `npm run build`). The one
still-manual file is the package's `index.js` — a runtime fallback re-export list
for consumers that don't externalise the import; the vite plugin externalises
`@lunarphp/panel` to the global, so add-ons never load it, but keep it mirroring
ui.ts anyway.

### npm packages and the workspace

Two npm packages are published for add-on authors and depended on by version
(never a `file:` path, which would only resolve inside this monorepo):

- `@lunarphp/panel` (`resources/panel-package/`) — the layout/page components above.
- `@lunarphp/panel-vite-plugin` (`resources/package/`) — the IIFE build preset.

This package's own frontend build package (`packages/panel/package.json`) is the
private, unpublished `@lunarphp/panel-build` — kept distinct so the public name is
free for the add-on-facing package. The monorepo root `package.json` declares an
npm workspace over these two packages plus `packages/panel-addon-example`, so the
example resolves the version-ranged deps to the local source during development.
Run `npm install` at the monorepo root (not inside the example) to link them.

## Translations

Panel strings live in `resources/lang/{locale}/{group}.php` across 16 locales
and are served to vue-i18n by the translations endpoint as `{group}.{key}`
messages. Add-ons keep their strings in their own Laravel lang namespace and
opt in via `Section::langNamespaces()` (or `Panel::translations()`); the
endpoint then serves those groups as `{namespace}::{group}` keys, falling back
per namespace to the app fallback locale. Server-side surfaces (nav labels,
flash messages) take the same lang keys through `__()`. Staff pick their panel
language from the user menu; the choice persists as `staff.preferred_locale`
and is applied by the `Authenticate` middleware.

## Ordering

Navigation items, table columns, and all action types carry a `Position`
(`Lunar\Panel\Support\Position`): `priority(int)` for coarse ordering, or
`before(key)` / `after(key)` to anchor relative to another entry. The shared
`OrderResolver` resolves the combined set, so an add-on can place work precisely
without guessing priority numbers. Anchors with a missing target fall back to
priority and log a warning.
