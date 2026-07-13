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

## Ordering

Navigation items, table columns, and all action types carry a `Position`
(`Lunar\Panel\Support\Position`): `priority(int)` for coarse ordering, or
`before(key)` / `after(key)` to anchor relative to another entry. The shared
`OrderResolver` resolves the combined set, so an add-on can place work precisely
without guessing priority numbers. Anchors with a missing target fall back to
priority and log a warning.
