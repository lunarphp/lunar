# Lunar Panel Addon Example

A minimal, standalone reference add-on for `lunarphp/panel`. It is a
separately-versioned, separately-compiled Composer + npm package proving that
the panel's runtime extension mechanism works end-to-end, without the panel
itself needing to recompile.

It doubles as a starter template — fork it with
`composer create-project lunarphp/panel-addon-example my-addon` and replace the
example page, slot, and column with your own.

Every code snippet below is taken from this package's own source (some are
trimmed for brevity) — read the linked file if you want the full context.

## What this proves

- **Page registration** — `resources/js/pages/Widgets/Index.vue` is
  registered under the namespaced name `example-addon::Widgets/Index` via
  `window.LunarPanel.registerPages()`, and served from a route the add-on
  registers itself.
- **Navigation registration** — the add-on adds its own nav group/item
  pointing at that route.
- **Settings screen registration** — `resources/js/pages/Settings/Index.vue`
  is a screen in the panel's Settings section, with its own sidebar
  group/item registered via `Section::settingsNavigation()` and rendered
  inside the panel's `SettingsShell`.
- **Slot registration** — `InfoBanner.vue` is registered as
  `example-addon::InfoBanner` and injected into the real Customers edit
  page's `customers.edit:main:after` zone.
- **Table extension registration** — `ExampleTableExtension` adds an extra
  column, a toolbar filter, a row action (anchored `Position::after('edit')`),
  and a bulk action to the real Customers index (`customers.index`).
- **Page action registration** — `ImportPageAction` (listing header) and
  `AuditPageAction` (record header, built from the `$context` record) appear
  in the real Customers pages' header ellipsis via `Section::pageActions()`.
- **Dashboard widget registration** — `CustomerCountWidget` contributes a
  card to the panel dashboard via `Section::widgets()`: its PHP `data()`
  ships as a deferred Inertia prop and its Vue body
  (`example-addon::CustomerCountWidget`) renders inside the panel-owned card
  chrome, reorderable and hideable per staff member like any first-party
  widget.
- **IIFE compilation** — `resources/js/addon.ts` compiles to a single IIFE via
  the panel's exported `@lunarphp/panel-vite-plugin`, sharing the panel's own
  Vue instance (`window.Vue`) instead of bundling a second copy.
- **Translations** — `resources/lang/{en,fr}/example.php` are ordinary Laravel
  lang groups, opted into the panel's translations endpoint via
  `Section::langNamespaces()`; the nav label resolves server-side through
  `__()` and the page translates client-side through the panel's shared
  vue-i18n instance (`t('example-addon::example.title')`).

`tests/panel/Feature/ExampleAddonTest.php` in the monorepo exercises all of
the above against this package's real, unmodified source — including
registering this add-on's service provider and hitting the *real* Customers
routes (`/panel/customers`, `/panel/customers/{id}/edit`) to prove the
extension points actually integrate, not just that they work in isolation.

## Scaffolding a new add-on package

An add-on is a normal Composer package with a Laravel service provider. This
package's own `composer.json`:

```json
{
    "name": "lunarphp/panel-addon-example",
    "description": "Starter template and reference add-on for lunarphp/panel: a page, a nav item, a slot, and a table extension registered at runtime without recompiling the panel. Fork it with `composer create-project lunarphp/panel-addon-example`.",
    "license": "MIT",
    "type": "project",
    "autoload": {
        "psr-4": {
            "LunarPanelExample\\": "src/"
        }
    },
    "require": {
        "php": "^8.4",
        "lunarphp/panel": "self.version"
    },
    "extra": {
        "laravel": {
            "providers": [
                "LunarPanelExample\\ExampleAddonServiceProvider"
            ]
        }
    }
}
```

The `extra.laravel.providers` entry lets Laravel's package auto-discovery
register the provider without the host app touching `config/app.php`. The
provider itself (`src/ExampleAddonServiceProvider.php`) registers a `Section`
with the panel and a Vite module:

```php
class ExampleAddonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Panel::section(new ExampleSection);

        $this->app->make(PanelManager::class)->vite('example-addon', [
            'input' => 'resources/js/addon.ts',
            'hotFile' => null,
            'buildDirectory' => 'vendor/lunar-panel/example-addon',
            // Lets `php artisan lunar:panel:link` symlink this package's
            // compiled build/ into public/vendor/lunar-panel/example-addon.
            '__buildSourcePath' => dirname(__DIR__).'/build',
        ]);
    }
}
```

`PanelManager::vite()` is what makes the panel's `app.blade.php` emit a
`<script>` tag for the add-on's compiled bundle automatically — no panel
changes required for a new add-on to ship its own JS.

## Registering a page and a nav item

Both live on a `Section` subclass (`src/ExampleSection.php`), which extends
`Lunar\Panel\Sections\Section`:

```php
private const PERMISSION = 'sales:manage-customers';

public function navigation(NavigationRegistry $registry): void
{
    $registry->group('example-addon-group', 'Example Add-on');
    $registry->addItem('example-addon-group', new NavigationItem(
        key: 'example-addon',
        label: 'Example Add-on',
        // Icon names come from the panel's built-in set (see the panel's
        // Icon.vue); add-ons cannot register their own SVGs.
        icon: 'tag',
        route: 'panel.example-addon.index',
        permission: self::PERMISSION,
    ));
}

public function routes(): ?Closure
{
    return function (): void {
        Route::middleware('can:'.self::PERMISSION)->group(function (): void {
            Route::get('example-addon', fn () => Inertia::render('example-addon::Widgets/Index', [
                'message' => 'Hello from the example add-on! This page was registered at runtime via window.LunarPanel.registerPages(), not compiled into the panel.',
            ]))->name('panel.example-addon.index');
        });
    };
}
```

`routes()` returns a `Closure` rather than eagerly registering routes,
because `PanelManager` only runs it inside the panel's own route group (so it
picks up the panel's URL prefix, guard, and middleware). The Inertia
component name is namespaced (`example-addon::Widgets/Index`) to match the
key the add-on's JS registers the page under — see
[Compiling the add-on bundle](#compiling-the-add-on-bundle-with-vite) below.

Note the authorization pattern: the panel's `Authenticate` middleware only
proves the visitor is signed-in staff, so an add-on gates its own routes with
`can:` middleware and declares the **same** permission handle on its
navigation item — what a user sees and what they can reach stay in lockstep.
This add-on extends the Customers area, so it reuses the core
`sales:manage-customers` handle; an add-on with its own domain seeds and
uses its own handle.

### Using the standard page layout

An add-on page should look like a first-party page. Two things make that work
without bundling any panel UI:

- **The sidebar shell is auto-applied.** The panel wraps every add-on page in
  its `PanelLayout` (the persistent layout), so the page renders inside the real
  panel chrome — nav sidebar, mobile drawer, collapse toggle — without importing
  or wrapping anything.
- **Panel components are imported from `@lunarphp/panel`.** The panel exposes a
  page-building set at runtime — layout/chrome (`PageHeader`, `PageZone`,
  `Breadcrumbs`, `SettingsShell`), data (`DataTable`, `Pagination`, `PageEmpty`,
  `StatusBadge`), filters/stats (`FilterDropdown`, `KpiCard`), form inputs
  (`TextInput`, `Select`, `Checkbox`, …), overlays (`Dialog`, `Slideout`,
  `ConfirmDialog`, `Tooltip`, `SideCard`, `Tabs`), and `Button`/`Icon`. The add-on's
  vite plugin externalises the `@lunarphp/panel` import to the panel's own components
  (`window.LunarPanelUI`) exactly the way it externalises `vue`, so nothing is
  duplicated. `resources/js/pages/Widgets/Index.vue`:

```vue
<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { PageHeader, PageZone, Button } from '@lunarphp/panel';

defineProps<{ message?: string }>();

// Shared props the panel middleware provides to every page, add-on pages included.
const panelName = computed(() => (usePage().props.panel as { name?: string } | undefined)?.name ?? 'Lunar');
</script>

<template>
    <div data-screen-label="Example Add-on" class="contents">
        <!-- `icon` renders the standard header tile (names come from the panel's
             built-in set, matching the nav item); use the #icon slot instead for
             custom markup like an avatar. -->
        <PageHeader title="Example Add-on" description="…" icon="tag">
            <template #actions>
                <Button variant="primary" icon="plus">Example action</Button>
            </template>
        </PageHeader>

        <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
            <PageZone region="main" position="before" />
            <p class="text-[13px] text-ink-700">{{ message }} — {{ panelName }}</p>
            <Link href="/panel/customers">Customers</Link>
            <PageZone region="main" position="after" />
        </div>
    </div>
</template>
```

`PageHeader` carries the shared page-action ellipsis, so header actions an
add-on (or the host) registers for this page appear automatically. `PageZone`
declares slot zones on the page you own, so other add-ons can inject into it —
the same mechanism this package uses against the first-party Customers page.
`usePage()` and `<Link>` work because `@inertiajs/vue3` is externalised to the
panel's own Inertia instance (`window.InertiaVue3`) — the add-on never bundles
a second copy, which would read uninitialised state.

### Rendering a data table

`DataTable` is the same component every first-party listing page uses. The
add-on's route shares rows as a plain Inertia prop, where each row carries its
column values plus an `_actions` map of per-row URLs (see the widgets route in
`src/ExampleSection.php`); the page passes the static action descriptors — an
action only renders on rows whose `_actions` map resolved a URL for its key.
From `resources/js/pages/Widgets/Index.vue`:

```vue
<script setup lang="ts">
const columns = [
    { key: 'name', label: 'Widget', width: 'minmax(0,1.4fr)' },
    { key: 'status', label: 'Status' },
    { key: 'sales', label: 'Sales (30d)', width: '110px', align: 'right' as const },
];

const rowActions = [
    { key: 'ping', label: 'Ping', icon: 'refresh', method: 'get', primary: false },
];
</script>

<template>
    <DataTable :columns="columns" :rows="widgets ?? []" :row-actions="rowActions" empty-text="No widgets yet">
        <template #cell-status="{ value }">
            <StatusBadge :tone="value === 'active' ? 'sage' : 'archived'" size="sm">{{ value }}</StatusBadge>
        </template>
    </DataTable>
</template>
```

A named `#cell-{key}` slot overrides how that column renders each cell (the
status badge above); columns without a slot render their raw row value as
text. This is your own page's table — to add columns or actions to a
*first-party* table instead, use a `TableExtension` (below).

## Adding a settings screen

The panel's Settings section extends the same way as the main sidebar: the
`Section` overrides `settingsNavigation()` (mirroring `navigation()`, but
driving the Settings sidebar) and registers routes under a `settings/...`
prefix. From `src/ExampleSection.php`:

```php
public function settingsNavigation(NavigationRegistry $registry): void
{
    $registry->group('example-addon', 'example-addon::example.settings_group');
    $registry->addItem('example-addon', new NavigationItem(
        key: 'example-addon-settings',
        label: 'example-addon::example.settings_label',
        route: 'panel.settings.example-addon.index',
        permission: self::PERMISSION,
    ));
}
```

An add-on can create its own group (as here) or add items to a first-party
one (e.g. `general`). The item appears in the Settings sidebar for any staff
member holding the permission; the `settings` entry route redirects to the
first settings page the user can see, so an add-on item is reachable even if
it's the only one.

The page itself (`resources/js/pages/Settings/Index.vue`) renders inside
`<SettingsShell>`, which scaffolds the whole screen the way first-party
settings pages get it: the Settings sidebar, a `Settings > {title}` breadcrumb
trail, the standard page header (`title`, optional `description`, your
`#actions` buttons, and the shared page-action ellipsis), flash message
display (so a route's `back()->with('success', …)` renders with no page
code), and a centered content column (pass `wide` for the full listing width
top-level pages use). One gotcha: `SettingsShell` is the page's *entire*
chrome, so the page must opt out of the `PanelLayout` the panel would
otherwise auto-apply, by declaring a no-op persistent layout:

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { SettingsShell, TextInput, Toggle, FieldLabel, Button } from '@lunarphp/panel';

// SettingsShell replaces the auto-applied PanelLayout chrome wholesale, so
// opt out with a no-op persistent layout — the resolver leaves an add-on
// page with its own layout alone.
defineOptions({
    layout: (_h: unknown, page: unknown) => page,
});

const props = defineProps<{
    settings: { webhook_url: string | null; ping_enabled: boolean };
    urls: { update: string };
}>();

const form = useForm({
    webhook_url: props.settings.webhook_url ?? '',
    ping_enabled: props.settings.ping_enabled,
});
</script>

<template>
    <SettingsShell title="Widget pings" description="What this screen configures.">
        <form @submit.prevent="form.post(props.urls.update, { preserveScroll: true })">
            <!-- TextInput / Toggle / Button fields, as on any panel form -->
        </form>
    </SettingsShell>
</template>
```

This example's settings values round-trip through the session so the demo
stays side-effect free; a real add-on would persist them to its own model or
config in the `POST` route (see the `settings/example-addon` routes in
`src/ExampleSection.php`).

## Registering a slot and the zone-naming convention

Slots let an add-on inject a component into a specific spot on a page it
doesn't own. `src/ExampleSection.php`:

```php
public function slots(SlotRegistry $registry): void
{
    // Demonstrates the slot mechanism on the Customer edit page. The zone prefix must
    // match the page's route name with the "panel." prefix stripped — our route is
    // named panel.customers.edit (there's no separate "show" route), so the zone is
    // "customers.edit", not the spec's illustrative "customers.show".
    $registry->add(new Slot(
        zone: 'customers.edit:main:after',
        component: 'example-addon::InfoBanner',
        props: ['message' => 'This banner was injected by the example add-on via a slot.'],
    ));
}
```

A zone name is `{section}.{page}:{region}[:position]`:

- `{section}.{page}` — the panel route name for the page you're targeting,
  **with the `panel.` prefix stripped**. `HandlePanelInertiaRequests` derives
  the current page's prefix the same way (from `$request->route()->getName()`),
  so the zone only matches if this segment is exactly right.
- `{region}` — a named slot inside that page's Vue template (e.g. `main`).
- `[:position]` — an optional qualifier the page template defines (e.g.
  `before` / `after`).

**This is the gotcha this add-on was built to catch**: it's tempting to guess
a zone name from what the page *does* (a "show" page) rather than what its
*route is actually named*. The real Customers edit route is named
`panel.customers.edit` (there is no separate `panel.customers.show`), so the
correct zone prefix is `customers.edit`, not `customers.show`. A slot
registered against the wrong prefix silently never renders — there's no
error, because `SlotRegistry::forPage()` just won't find a match. If your
slot isn't appearing, this is the first thing to check (see
[Troubleshooting](#troubleshooting)).

## Registering a table extension

A `TableExtension` bundles one or more `TableColumn`s (plus optional filters
and actions) and is registered against a table ID — here, the real Customers
index table:

```php
// src/ExampleSection.php
public function tableExtensions(): array
{
    return ['customers.index' => ExampleTableExtension::class];
}
```

```php
// src/Tables/ExampleTableExtension.php
class ExampleTableExtension extends TableExtension
{
    public function columns(): array
    {
        return [ExampleColumn::class];
    }
}
```

```php
// src/Tables/ExampleColumn.php
class ExampleColumn extends TableColumn
{
    public function key(): string
    {
        return 'id';
    }

    public function header(): string
    {
        return 'ID (Example Add-on)';
    }
}
```

`Lunar\Panel\Http\Controllers\Customers\CustomerIndexController` calls
`PanelManager::resolveExtensions('customers.index')` and merges every
registered column's `key()`/`header()` onto the first-party column list, and
(if a column overrides `query()`) applies that hook to the Eloquent query
before pagination — see `Lunar\Panel\Tables\TableColumn::query()` if your
column needs a computed value (e.g. a `withCount()`).

A column renders its value as plain text by default. Override `type()` with a
`ColumnType` (`badge`, `date`, `boolean`, `currency`, `image`) for a generic
renderer, or `component()` with a namespaced name registered via
`window.LunarPanel.registerComponents()` for a fully custom cell — the
component receives `row` and `value` props.

Filters extend the same way: return `TableFilter` classes from `filters()`.
A filter declares dropdown `options()` (`[submitted value => label]`) and a
`query()` hook; the panel renders it in the table toolbar next to the
first-party filters (with an automatic "All" default), submits the selection
as a nested `filter[{key}]` query param, and applies `query()` server-side
before pagination. From `src/Tables/HasAccountRefFilter.php`:

```php
class HasAccountRefFilter extends TableFilter
{
    public function key(): string
    {
        return 'has_account_ref';
    }

    public function label(): string
    {
        return 'Account ref (Example)';
    }

    public function options(): array
    {
        return [
            'yes' => 'Has account ref',
            'no' => 'No account ref',
        ];
    }

    public function query(Builder $query, mixed $value): void
    {
        $value === 'yes'
            ? $query->whereNotNull('account_ref')->where('account_ref', '!=', '')
            : $query->where(fn (Builder $inner) => $inner->whereNull('account_ref')->orWhere('account_ref', ''));
    }
}
```

A `TableExtension` can also override `searchQuery(Builder $query, string $term)`
to extend the page's keyword search — the hook is called inside the page's own
search `where` group, so add `orWhere` clauses for the fields your extension
makes searchable.

### Row actions

Return `TableAction` classes from `actions()` to add entries to every row's
ellipsis menu on a first-party table. An action declares a `label()`, optional
`icon()` and `method()`, and builds its per-row URL from the record — return
`null` to omit the action from that row (that's how the first-party Delete
hides itself on protected records). From `src/Tables/PingRowAction.php`:

```php
class PingRowAction extends TableAction
{
    public function key(): string
    {
        return 'example-ping';
    }

    public function label(): string
    {
        return 'Ping (Example)';
    }

    public function icon(): ?string
    {
        return 'refresh';
    }

    public function position(): Position
    {
        return Position::after('edit');
    }

    public function method(): string
    {
        return 'get';
    }

    public function url(mixed $record = null): ?string
    {
        return $record ? route('panel.example-addon.ping', $record) : null;
    }
}
```

The `Position::after('edit')` anchor places the action immediately after the
built-in Edit entry — first-party Edit/Delete are ordinary `TableAction`s in
the same ordered set, so add-ons can position relative to them (see
[Ordering with `Position`](#ordering-with-position)). A destructive action
returns a `confirmationMessage()` to get a confirm dialog before dispatch, a
`permission()` key to hide itself from unauthorised staff, and `primary(): true`
to render as an inline button instead of collapsing into the ellipsis
(reserved by convention for a page's main verb — add-ons should normally stay
in the ellipsis).

### Bulk actions

Return `TableBulkAction` classes from `bulkActions()`. Registering any bulk
action is what makes the table's selection checkboxes appear; while rows are
checked, the toolbar is replaced by the bulk-action bar, and dispatching the
action posts the selected row ids (as `ids`) to the action's `url()`. From
`src/Tables/PingBulkAction.php`:

```php
class PingBulkAction extends TableBulkAction
{
    public function key(): string
    {
        return 'example-bulk-ping';
    }

    public function label(): string
    {
        return 'Ping selected (Example)';
    }

    public function method(): string
    {
        return 'post';
    }

    public function url(): ?string
    {
        return route('panel.example-addon.bulk-ping');
    }
}
```

`confirmationMessage()`, `permission()` and `position()` work exactly as they
do on row actions.

## Registering page actions

Where a `TableAction` targets rows, a `PageAction` targets a page's header —
"Import" above a listing, "Audit log" on a record page. Both cases are one
abstract keyed by page id (the same route-name-derived ids slots use); record
pages hand the route-bound model to `url()` as `$context`, listing pages hand
`null`. Registered via `Section::pageActions()`:

```php
// src/ExampleSection.php
public function pageActions(): array
{
    return [
        'customers.index' => [ImportPageAction::class],
        'customers.edit' => [AuditPageAction::class],
    ];
}
```

```php
// src/Actions/AuditPageAction.php — a record-page action
class AuditPageAction extends PageAction
{
    public function key(): string
    {
        return 'example-audit';
    }

    public function label(): string
    {
        return 'Audit log (Example)';
    }

    public function icon(): ?string
    {
        return 'fileText';
    }

    public function url(mixed $context = null): ?string
    {
        return $context ? route('panel.example-addon.audit', $context) : null;
    }
}
```

(`src/Actions/ImportPageAction.php` is the listing-page variant — identical
shape, static URL, `$context` ignored.)

Every content page renders its header through the shared page scaffold, which
always carries the page-action ellipsis — so a `PageAction` needs no
cooperation from the target page. Actions collapse into the header's "more
actions" ellipsis by default; `primary(): true` promotes one to an
always-visible header button. `method()`, `confirmationMessage()`,
`permission()` and `position()` mirror `TableAction`.

## Registering a dashboard widget

The panel dashboard is a per-staff grid of widgets; an add-on contributes one
by returning a `Widget` class from `Section::widgets()`:

```php
public function widgets(): array
{
    return [CustomerCountWidget::class];
}
```

`src/Dashboard/CustomerCountWidget.php` shows the shape. A widget declares:

- `key()` — its stable identity (order and visibility preferences store it).
- `component()` — the namespaced Vue component name, registered from the
  bundle exactly like a slot component
  (`window.LunarPanel.registerComponents('example-addon', { CustomerCountWidget })`).
- `label()` / `description()` / `icon()` — shown in the card header and the
  "Add a widget" dialog. Labels resolve server-side through `__()`, so use
  your namespaced lang keys.
- `span()` (`WidgetSpan::Half` or `Full`), `permission()`, `position()`
  (the shared `Position` primitive), and `visibleByDefault()`.
- `data(DashboardRange $range)` — the payload, computed server-side against
  the selected range (`$range->start()`, `->end()`, `->buckets()`, plus the
  previous-period equivalents). It ships as a **deferred Inertia prop in its
  own group**, so a slow widget never blocks the rest of the dashboard, and
  nothing is computed for widgets the staff member has hidden.

The dashboard owns the card chrome — header, drag handle, hide button — so the
component renders only the body. It receives `data` (the `data()` payload) and
`range` (the current range value) as props, and can build with the panel's
chart primitives (`TimeSeriesChart`, `Sparkline`, `DonutChart`, `KpiCard`)
from `@lunarphp/panel`. Staff reorder, hide, and re-add widgets per user; a
widget whose `permission()` the staff member lacks never reaches the page.

## Ordering with `Position`

Everything an add-on injects into an ordered set — navigation items, table
columns, filters, row actions, bulk actions, page actions — exposes
`position(): Position` (`Lunar\Panel\Support\Position`) and is sorted by one
shared resolver, so the same placement rules apply panel-wide:

- `Position::priority(int)` — coarse ordering; lower sorts first, ties keep
  registration order. First-party entries sit at predictable priorities, and
  unpositioned entries default to last.
- `Position::before('key')` / `Position::after('key')` — anchor immediately
  adjacent to another entry in the same set by its key, first-party or
  add-on. This is how `PingRowAction` sits right after the built-in `edit`.

An anchor whose target key doesn't exist falls back to priority ordering and
logs a warning (nothing throws — the entry still renders). Prefer anchors
when you care about a neighbour, priorities when you only care about
roughly-where.

## Extending an existing section with `SectionExtension`

A `Section` owns a key and appears as its own area; a `SectionExtension`
grafts onto a section someone else owns. It supports the same hooks —
`navigation()`, `settingsNavigation()`, `routes()`, `tableExtensions()`,
`pageActions()`, `slots()`, `vite()`, `langNamespaces()` — plus `extends()`
naming the target section key:

```php
class SalesExtension extends SectionExtension
{
    public function extends(): string
    {
        return 'sales';
    }

    // ... any of the Section hooks
}
```

Register it with `Panel::extendSection(new SalesExtension)`. Use a `SectionExtension`
when your add-on is conceptually part of an existing area (extra navigation
under Sales, say); use your own `Section` when it stands alone. An `extends()`
key that matches no registered section logs a warning and the extension is
skipped, so load order between add-ons never throws. (This example package
uses a full `Section`; the monorepo's `tests/panel/Fixtures/Addon/` shows a
worked `SectionExtension`.)

## Translating an add-on

Add-on strings live in ordinary Laravel lang groups — no JS-side message
files to maintain. Three steps:

1. **Register the translator namespace** in your service provider:

   ```php
   $this->loadTranslationsFrom(dirname(__DIR__).'/resources/lang', 'example-addon');
   ```

2. **Opt the namespace into the panel frontend** on your `Section` (or
   `SectionExtension`):

   ```php
   public function langNamespaces(): array
   {
       return ['example-addon'];
   }
   ```

   The panel's translations endpoint then serves every group under the
   namespace as `example-addon::{group}` message keys, cached and versioned
   together with the panel's own strings. (Non-section code can call
   `Panel::translations('example-addon')` directly.)

3. **Use the keys on both sides.** Server-side surfaces (navigation labels,
   flash messages) take the key through Laravel's own `__()`/lang-key
   resolution — `label: 'example-addon::example.nav_label'`. Vue pages import
   `useI18n` from `vue-i18n` (externalised to the panel's shared instance by
   the vite plugin) and call `t('example-addon::example.title')`.

Ship whichever locales you support; a locale the add-on lacks falls back to
the app fallback locale for that namespace only, so a partially translated
add-on never blanks out the panel's own locale switcher. Staff pick their
panel language from the user menu (persisted per staff member as
`preferred_locale`).

## Compiling the add-on bundle with Vite

`package.json` and `vite.config.js`:

```json
{
    "name": "@lunarphp/panel-addon-example",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@inertiajs/vue3": "^2.0.0",
        "@lunarphp/panel": "^0.1.0",
        "@lunarphp/panel-vite-plugin": "^0.1.0",
        "@vitejs/plugin-vue": "^5.2.0",
        "vite": "^6.0.0",
        "vue": "^3.5.13"
    }
}
```

`@lunarphp/panel` (the panel's layout/page components) and
`@lunarphp/panel-vite-plugin` (the build preset) are published to npm with
each tagged Lunar release (the `publish-npm` workflow), so a forked add-on
installs them from the registry like any dependency — no paths into the
panel's source. (Inside the Lunar monorepo itself, the root
`package.json` declares an npm workspace that resolves these two names to the
local package directories, so this example builds against the in-development
source without changing the version specifiers above.)

```js
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import lunarPanelPlugin from '@lunarphp/panel-vite-plugin';

// Compiles resources/js/addon.ts to a single IIFE bundle that shares the
// panel's Vue instance (window.Vue) instead of bundling its own copy.
export default defineConfig({
    plugins: [
        vue(),
        lunarPanelPlugin({ name: 'LunarPanelExampleAddon' }),
    ],
    build: {
        outDir: 'build',
        rollupOptions: {
            input: 'resources/js/addon.ts',
        },
    },
});
```

`@lunarphp/panel-vite-plugin` (the panel's `packages/panel/resources/package/vite-plugin.js`)
forces `output.format: 'iife'` and externalises `vue` (to the `window.Vue`
global), `@inertiajs/vue3` (to `window.InertiaVue3`), and `@lunarphp/panel`
(to `window.LunarPanelUI`), each published by the panel's own `app.ts` at
startup. That is what lets the add-on's bundle call into the panel's Vue and
Inertia runtimes and reuse its layout/page components instead of shipping
second copies.

The add-on's entry point (`resources/js/addon.ts`) registers its page and
slot components:

```ts
import WidgetsIndexPage from './pages/Widgets/Index.vue';
import InfoBannerComponent from './components/InfoBanner.vue';

// Register eagerly. The panel's app.ts publishes window.LunarPanel and is emitted
// before any add-on script, so it is always present here. Registration MUST happen
// before the panel's first render — the panel holds its initial mount until
// DOMContentLoaded (after which all add-on scripts have run), so anything registered
// at the top level of an add-on bundle is guaranteed to be in place in time.
window.LunarPanel.registerPages({
    'example-addon::Widgets/Index': WidgetsIndexPage,
});

window.LunarPanel.registerComponents('example-addon', {
    InfoBanner: InfoBannerComponent,
});
```

Register pages, components, layouts, and translations at the top level like this —
never inside `window.LunarPanel.booting()`. `booting()` callbacks run *after* the
panel has mounted, which is too late: a page or slot component registered there is
missing when Inertia resolves and first renders it on a hard load, and because
registration is not reactive the slot never recovers. Reserve `booting()` for work
that genuinely needs the mounted app, not for registration.

### The rest of the runtime API

Beyond `registerPages()`, `registerComponents()` and `booting()`, the runtime
exposes:

- `registerLayout(name, component)` — register a persistent layout an add-on
  page can opt into; the panel's default `PanelLayout` is the `default` entry
  in the same registry, applied automatically to add-on pages that declare no
  layout of their own.
- `registerTranslations(locale, namespace, messages)` — push vue-i18n messages
  for your namespace directly at runtime, merging over the endpoint-served
  set. Prefer [`langNamespaces()`](#translating-an-add-on) (PHP lang files) so
  your strings version and cache with everyone else's; this is the escape
  hatch for messages that only exist client-side.
- `resolveExtensionComponent(name)` — look up a namespaced component
  registered by any bundle, the same resolution `PanelSlot` and
  component-rendered table columns use.

The typed contract for all of this is the `LunarPanelRuntime` interface,
shipped as `dist/runtime.d.ts` in the `@lunarphp/panel` package and referenced
from its main types — importing anything from `@lunarphp/panel` types
`window.LunarPanel` in your editor for free.

## Installing into a host app

1. `composer require lunarphp/panel-addon-example` to install it as-is, or
   `composer create-project lunarphp/panel-addon-example my-addon` to fork it
   as the starting point for your own add-on.
2. Register `LunarPanelExample\ExampleAddonServiceProvider` (auto-discovered
   via `composer.json`'s `extra.laravel.providers`, or add it manually).
3. `npm install` inside this package, then `npm run build`. This produces a
   compiled IIFE + manifest in `build/`.
4. Get the compiled build into `public/vendor/lunar-panel/example-addon/`:
   - **Production**: `php artisan vendor:publish --tag=example-addon-panel-assets --force`
     copies it (the panel registers a `{key}-panel-assets` publish tag for
     every module's `__buildSourcePath`; `--tag=panel-all-assets` publishes
     the panel's own build plus every add-on in one go).
   - **Local development**: `php artisan lunar:panel:link` symlinks the
     `build/` directory instead, so a rebuild is picked up without
     re-publishing.
5. The panel's `app.blade.php` loops `PanelManager::registeredVites()` and
   emits a `<script>`/`<link>` tag for every registered module automatically —
   no panel changes required.

## Testing an add-on against the real panel

The monorepo's `tests/panel/Fixtures/ExampleAddonTestCase.php` shows the
pattern for testing an add-on against the panel's real Testbench harness:
register the add-on's own service provider in `getPackageProviders()`, and
add its Inertia page directory to `inertia.testing.page_paths` so
`assertInertia()` can resolve its components. `tests/panel/Feature/ExampleAddonTest.php`
then hits both the add-on's own route (`/panel/example-addon`) and the real
Customers routes (`/panel/customers`, `/panel/customers/{id}/edit`) to prove
the table extension column and slot entry actually appear on the first-party
pages, not only in an isolated fixture.

## Troubleshooting

**A registered page/slot/column doesn't appear.**

- **Slot never renders**: check the zone prefix (`{section}.{page}` before
  the first `:`) against the *route name* of the page you're targeting, with
  `panel.` stripped — not a guessed name based on what the page does. This
  session's own cautionary example: the Customers edit page's route is named
  `panel.customers.edit`, so the correct zone prefix is `customers.edit` —
  registering against `customers.show` (a name that doesn't exist as a route)
  causes the slot to silently never match, with no error anywhere.
- **Page component "not found" client-side**: confirm the Inertia component
  name passed to `Inertia::render()` in your route matches the key used in
  `window.LunarPanel.registerPages({ 'namespace::Path': Component })` exactly,
  including the `namespace::` prefix.
- **Settings page renders with a doubled sidebar** (main nav wrapped around
  the settings shell): the page uses `<SettingsShell>` but didn't opt out of
  the auto-applied `PanelLayout`. Declare the no-op persistent layout shown in
  [Adding a settings screen](#adding-a-settings-screen).
- **Table extension column missing**: confirm the table ID string passed to
  `Section::tableExtensions()` (e.g. `'customers.index'`) matches the ID the
  controller passes to `PanelManager::resolveExtensions()` — these are plain
  strings with no central registry, so a typo produces no error, just a
  column that never appears.
- **`ReferenceError: InertiaVue3` (or `Vue` / `LunarPanelUI`) `is not defined`**
  thrown from the add-on bundle: the served panel bundle is older than the
  vite plugin the add-on was compiled with, so a `window` global the add-on
  expects is never published — and the crash happens before the add-on
  registers anything, so it cascades into "Panel page not found" and
  unregistered-slot warnings. Re-publish the panel's compiled assets in the
  host app (`php artisan vendor:publish --tag=panel-assets --force`; during
  monorepo development, symlink the package's `public/build` instead) so the
  panel and the add-on agree on the published globals.
- **Add-on JS never runs**: check the compiled bundle is actually being
  served at the `buildDirectory` path passed to `PanelManager::vite()`, and
  that your registration calls run at the **top level** of the bundle — not
  inside `window.LunarPanel.booting()`, whose callbacks run after the first
  render and are therefore too late for page and slot registration (see
  [Compiling the add-on bundle](#compiling-the-add-on-bundle-with-vite)).

## Files

- `src/ExampleAddonServiceProvider.php` — registers the section and the Vite
  module.
- `src/ExampleSection.php` — the `Section` implementation (key, navigation,
  settings navigation, routes, slots, table extensions, page actions, lang
  namespaces).
- `src/Tables/ExampleColumn.php` / `HasAccountRefFilter.php` /
  `ExampleTableExtension.php` — the `customers.index` table extension.
- `src/Tables/PingRowAction.php` / `PingBulkAction.php` — the row and bulk
  actions injected into the first-party customers table.
- `src/Actions/ImportPageAction.php` / `AuditPageAction.php` — the listing-
  and record-page header actions.
- `resources/js/addon.ts` — the IIFE entry point.
- `resources/js/pages/Widgets/Index.vue`, `resources/js/components/InfoBanner.vue`
  — the example page and slot component.
- `resources/js/pages/Settings/Index.vue` — the example settings screen.
