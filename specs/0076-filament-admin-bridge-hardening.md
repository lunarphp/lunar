# 0076 — Filament admin and bridge hardening

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-09-01
- TODO item: Filament admin & bridge hardening — standalone bridge, dead hooks and config, locale nav groups, guard and asset id (spec 0076)

## Problem

Writing the v2 Filament docs (lunarphp/docs#45) meant verifying every
documented claim against source. That pass surfaced a set of defects in
`lunarphp/admin` and `lunarphp/filament` — some contradict what the docs and
READMEs promise, others are silent traps for consumers. Collected here as one
hardening pass, in the spirit of the port-fixes bucket ([[0065-port-v1.5-fixes]]),
because each is small on its own and they all live in the same two packages.

1. **The bridge is not standalone.** Its pitch — and the docs' — is that every
   `lunarphp/filament` class works in any Filament v5 panel without
   `lunarphp/admin`. Two ship in the bridge but cannot:
   - `Lunar\Filament\Infolists\Components\Timeline` renders
     `timeline.blade.php`, which `@livewire`s
     `Lunar\Admin\Livewire\Components\ActivityLogFeed`
     (`packages/filament/resources/views/infolists/components/timeline.blade.php:7`)
     — an admin-package component built on the admin's ActivityLog manifest.
     A bridge-only panel using `Timeline` fatals at render.
   - `Lunar\Filament\Actions\Support\DownloadPdfAction` (and its
     `Orders\DownloadOrderPdfAction` subclass) imports the dompdf facade, but
     `barryvdh/laravel-dompdf` is declared only in `packages/admin/composer.json`,
     and the `lunar.pdf.download` route the action links to is registered only
     by the admin (`packages/admin/src/LunarPanelManager.php:269`). Bridge-only
     use fatals on the missing facade, or 404s on the missing route.

2. **Dead extension hooks.** `ResourceExtension::extendTable()`
   (`packages/admin/src/Support/Extending/ResourceExtension.php:14`) and
   `ListPageExtension::relationManagers()`
   (`packages/admin/src/Support/Extending/ListPageExtension.php:22`) are
   declared on the base classes but dispatched from nowhere — overriding them
   is a silent no-op. The working seams are `configureTable` targeting the
   bridge table class, and `ResourceExtension::getRelations()`. Conversely,
   `extendsInfolist` **is** dispatched
   (`packages/admin/src/Support/Pages/BaseViewRecord.php:66`) but
   `ViewPageExtension` never declares it, so the hook is undiscoverable from
   the base class consumers are told to extend.

3. **Navigation groups break on non-English locales.** The panel registers its
   groups as English literals — `'Catalog'`, `'Sales'`, and a `'Settings'`
   `NavigationGroup` (`packages/admin/src/LunarPanelManager.php:317`) — while
   every resource returns a translated group name
   (`ProductResource::getNavigationGroup()` returns
   `__('lunarpanel::global.sections.catalog')`). The strings only coincide in
   English; on the other 15 locales resources no longer bind to the registered
   groups, so group ordering and the collapsed Settings state are lost.

4. **The staff guard is hard-coded.** Core promises the guard is configurable
   (`lunar.staff.guard`, `packages/core/config/staff.php`), and registers
   guard, provider, and password broker from that config. The Filament panel
   ignores it: `->authGuard('staff')`
   (`packages/admin/src/LunarPanelManager.php:288`). A renamed guard signs in
   nobody. `Staff::$guard_name` is likewise pinned to `'staff'`, which is what
   Spatie permissions resolve against.

5. **Admin CSS registered under the wrong package id.** The admin's stylesheet
   registers as `Css::make('lunar-panel', …)` under the package id
   `'lunarphp/panel'` (`packages/admin/src/LunarPanelManager.php:298`) — the
   Inertia panel's package name. Published asset paths and any
   per-package asset lookups point at a package that ships no Filament assets.

6. **Dead configuration.**
   - `lunar.admin.order_count_statuses` (`packages/admin/config/admin.php:15`)
     is read nowhere; the order navigation badge queries
     `whereNull('closed_at')` directly.
   - `LunarPlugin::globalSearch()` / `actions()` only persist
     `lunar.filament.plugin.global_search` / `.actions` config flags that no
     code reads — the resources and actions they claim to toggle are wired
     unconditionally.
   - `lunar.filament.register_widgets_on_default_panel`
     (`packages/filament/config/filament.php:44`) is advertised in the bridge
     README as opt-in widget auto-registration but is read nowhere.

7. **Untagged publishes.** The admin's views and lang publishes carry no tag
   (`packages/admin/src/LunarPanelProvider.php:45`), so neither can be
   published selectively — unlike core's `lunar.translation` /
   `lunar.migrations` and the admin's own `lunarpanel.pdf`.

## Proposal

### A. Restore the bridge's standalone guarantee

Both leaked classes are features of the shipped admin shell, not reusable
building blocks — they move back to `lunarphp/admin`:

- `Timeline` returns to its v1 home, `Lunar\Admin\Support\Infolists\Components\Timeline`,
  with `timeline.blade.php` moving to the admin's views. The
  `LunarSetList` entry mapping the v1 name to the bridge
  (`packages/upgrade/src/Rector/LunarSetList.php:405`) becomes an identity
  rename and is deleted.
- `DownloadPdfAction` and `DownloadOrderPdfAction` move to
  `Lunar\Admin\Support\Actions\DownloadPdfAction` and
  `Lunar\Admin\Support\Actions\Orders\DownloadOrderPdfAction`, beside the
  route, the dompdf dependency, and the publishable PDF views they depend on.
  The deprecated `Lunar\Admin\Support\Actions\PdfDownload` shim re-targets the
  admin class. No set-list change — the v1 name never left the admin package.

A new architecture test in the filament suite enforces the boundary from now
on: no class or view under `packages/filament` may reference `Lunar\Admin\`.

### B. True up the extension hook surface

- Delete `ResourceExtension::extendTable()` — the seam is `configureTable`
  against the bridge table class.
- Delete `ListPageExtension::relationManagers()` — the seam is
  `ResourceExtension::getRelations()`.
- Declare `extendsInfolist(Schema $schema): Schema` on `ViewPageExtension`,
  matching the hook `BaseViewRecord` already dispatches.

The removals get a note in the upgrade guide's extension section; Rector
cannot rewrite a behavioural hook, and the replacement seams take different
targets.

### C. Locale-safe navigation groups

Register the groups from the same lang keys the resources use, with
closure labels so they evaluate per request:

```php
->navigationGroups([
    NavigationGroup::make()->label(fn (): string => __('lunarpanel::global.sections.catalog')),
    NavigationGroup::make()->label(fn (): string => __('lunarpanel::global.sections.sales')),
    NavigationGroup::make()->label(fn (): string => __('lunarpanel::global.sections.settings'))->collapsed(),
])
```

All three keys already exist across the 16 locales; no translation work.
A test registers the panel under a non-English locale and asserts each
resource's group matches a registered group.

### D. Honour the configured staff guard

- `LunarPanelManager` reads `config('lunar.staff.guard', 'staff')` for
  `authGuard()`.
- `Staff` resolves its Spatie guard from the same config (a `guardName()`
  method replacing the pinned `$guard_name` property) so permission checks
  follow the rename. This is a core change; the panel and admin both consume
  it.

### E. Correct the asset package id and tag the publishes

- The admin's CSS registers under `'lunarphp/admin'`. Released alphas must
  re-run `php artisan filament:assets` after upgrading (the published asset
  path changes); called out in the release notes.
- The views and lang publishes gain `lunarpanel.views` and
  `lunarpanel.translations` tags, following core's naming.

### F. Remove the dead configuration

- Delete `order_count_statuses` from `packages/admin/config/admin.php`. The
  badge's `whereNull('closed_at')` is the v2 semantic — "open orders" — and
  status strings are free-form in v2, so a status allow-list is the wrong
  shape. Consumers wanting a different badge override
  `OrderResource::getNavigationBadge()`.
- Remove the `globalSearch()` / `actions()` toggles from `LunarPlugin` and the
  config writes behind them; `fullPreset()` reduces to the two real feature
  groups (`widgets()`, `livewireComponents()`).
- Delete `register_widgets_on_default_panel` and its README section — widgets
  register through `LunarPlugin::widgets()`.

The bridge README and the v2 docs (Add-ons > Filament Admin) update to match.

## Alternatives considered

- **Make the bridge self-sufficient instead of moving the leaked classes** —
  declare dompdf in the bridge, have it register the download route, move
  `ActivityLogFeed` (and the ActivityLog manifest it needs) down. Rejected:
  it drags a PDF engine onto every bridge consumer and pulls admin-shell
  infrastructure into a package whose value is being thin. Bespoke panels
  can wire their own PDF action against the core order data.
- **Wire `order_count_statuses` into the badge** rather than deleting it.
  Rejected: v2 order lifecycle is fulfilment-centric (`closed_at`), and
  resurrecting a status allow-list would encode the v1 model the line moved
  away from.
- **Keep `extendTable()` and wire it up.** Rejected: table definitions belong
  to the bridge table classes with their own `configureTable` dispatch; a
  second resource-level seam for the same mutation invites ordering
  ambiguity.
- **Leave the guard hard-coded and document the constraint.** Rejected: core
  already registers guard/provider/broker from config, so the promise exists;
  the admin is the only consumer not honouring it.
- **Do nothing** — every item stays a divergence between what the docs can
  honestly say and what the packages claim.

## Migration impact

- Database migrations: none.
- Breaking changes (all alpha-period surface):
  - `Lunar\Filament\Infolists\Components\Timeline`,
    `Lunar\Filament\Actions\Support\DownloadPdfAction`, and
    `Lunar\Filament\Actions\Orders\DownloadOrderPdfAction` move to
    `lunarphp/admin`. v1 consumers are unaffected (v1 names either keep
    working via the restored namespace or the existing shim); alpha adopters
    get an upgrade-guide note.
  - `LunarPlugin::globalSearch()` / `actions()` removed; `fullPreset()`
    behaviour is unchanged in effect.
  - Config keys `lunar.admin.order_count_statuses` and
    `lunar.filament.register_widgets_on_default_panel` removed.
  - `ResourceExtension::extendTable()` and
    `ListPageExtension::relationManagers()` removed (both were no-ops).
- Rector / upgrade package: delete the now-identity `Timeline` rename
  (`LunarSetList.php:405`); upgrade-guide notes for the hook removals and
  class moves.
- Translation / locale impact: none — reuses existing
  `lunarpanel::global.sections.*` keys in all 16 locales.
- Filament / admin impact: re-run `filament:assets` after upgrading (asset id
  change); no resource or schema surface changes.
- Docs: lunarphp/docs Add-ons > Filament pages and the bridge README drop the
  removed toggles and gain the corrected class homes.

## Open questions

- Should `Staff::guardName()` fall back to the connection-level Spatie default
  when `lunar.staff.register_guard` is `false` (consumer-managed auth), or
  always read `lunar.staff.guard`? Lean: always read the config — it is the
  single source of truth either way. Resolve during slice 4 review.

## References

- lunarphp/docs#45 review comment — the findings list this spec collects:
  https://github.com/lunarphp/docs/pull/45#issuecomment-5495907816
- [[0006-filament-bridge-package]] — the extraction that leaked `Timeline`
  and the PDF actions into the bridge.
- [[0010-filament-self-hosting-parity]] — Staff/auth move to core that this
  finishes for the guard config.
- [[0075-first-staff-account-creation]] — the first fix from the same findings
  list.

## Implementation plan

- [ ] Slice 1 — Bridge standalone: move `Timeline` + PDF actions to admin, set-list cleanup, filament-suite architecture test.
- [ ] Slice 2 — Hook surface: delete the two dead hooks, declare `extendsInfolist`, upgrade-guide notes.
- [ ] Slice 3 — Locale-safe navigation groups + test.
- [ ] Slice 4 — Configured staff guard through panel and `Staff::guardName()` + tests.
- [ ] Slice 5 — Asset package id fix and tagged views/lang publishes.
- [ ] Slice 6 — Config hygiene (`order_count_statuses`, plugin toggles, `register_widgets_on_default_panel`), README + docs follow-up.
