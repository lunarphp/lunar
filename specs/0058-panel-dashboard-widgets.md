# 0058 — Panel dashboard widgets

- Status: proposed
- Author: Glenn (via pairing session)
- Created: 2026-07-21
- TODO item: Panel dashboard — registrable widgets, per-staff layout, brand chart palette (spec 0058)

## Problem

The panel dashboard is a placeholder card (spec 0049 deliberately scoped widgets out).
The design prototype (`lunarphp/lunar-v2-ui`) has since validated a full dashboard: nine
widgets over a two-column grid, a range selector, and a customise mode where widgets are
dragged to reorder, hidden, or re-added. None of that exists in the panel, and — more
importantly — there is no extension point for it: an add-on (payments, reviews,
accounting) has nowhere to surface store-level insight on the landing page except the
generic `dashboard:main` slot zones, which are render-only and carry no identity,
ordering, visibility, or data semantics.

Separately, the panel's charts colour everything sage (`TimeSeriesChart` strokes
`--color-sage-ink`, the prototype dashboard reuses the same sage/warn tones). The Lunar
brand mark is a blue moon (`#4BC7F8` on `#122036`); the charts should read as Lunar, not
as a generic green admin.

## Proposal

Four parts: a widget extension point mirroring the panel's existing registry patterns, a
per-staff layout persisted server-side, the dashboard screen itself (rebuilt from the
prototype), and a brand-anchored chart palette with two new public chart components.

### 1. Widget extension point

A `Widget` abstract in `Lunar\Panel\Dashboard\`, registered via a `Section::widgets()`
hook or `Panel::widget()` — the same dual registration every other extension point uses.

```php
abstract class Widget
{
    abstract public function key(): string;        // e.g. 'revenue-chart', 'my-addon-sales'
    abstract public function component(): string;  // JS component: bare (first-party) or 'my-addon::SalesWidget'

    public function label(): string;               // customise dialog + card header, via __()
    public function description(): ?string;        // customise dialog
    public function icon(): ?string;               // panel icon name
    public function span(): WidgetSpan;            // WidgetSpan::Half (default) | WidgetSpan::Full
    public function flat(): bool;                  // true = no card chrome (the KPI row)
    public function permission(): ?string;         // hidden entirely when the staff member lacks it
    public function position(): Position;          // default ordering, shared OrderResolver
    public function visibleByDefault(): bool;      // default true

    /** @return array<string, mixed> props for the Vue component */
    abstract public function data(DashboardRange $range): array;
}
```

- **`WidgetRegistry`** collects widgets, permission-filters via the existing
  `$user->can()` convention, and orders through the shared `OrderResolver` — one
  ordering model panel-wide.
- **`DashboardRange`** is a string-backed enum (`Today`, `SevenDays`, `ThirtyDays`,
  `NinetyDays` → `today|7d|30d|90d`) exposing `bounds()`/`previousBounds()` and bucket
  helpers (hourly for `today`, daily otherwise), so every widget aggregates over the
  same window and comparison period.
- **Data delivery**: `DashboardController` shares the resolved widget list (key, label,
  icon, span, flat, component) as a plain prop, and each *visible* widget's `data()`
  result as a **deferred Inertia prop in its own group** (`widget:{key}`). Widgets load
  in parallel after first paint behind skeletons; a slow add-on widget cannot block
  first-party ones; hidden widgets' data is never computed. Range changes are partial
  reloads carrying a validated `range` query param.
- **Component resolution**: first-party names resolve from a local map in the dashboard
  page; namespaced names go through `window.LunarPanel.resolveExtensionComponent()`,
  exactly as `PanelSlot` does. Unresolvable components are skipped with a console
  warning.
- The card chrome (header with icon + label, drag handle, hide button) is owned by the
  dashboard grid, **not** the widget component — a widget renders body content only.
  This deviates from the prototype (whose widgets each wrap their own `WidgetCard`) so
  that add-on widgets get drag/hide/permission behaviour for free and cannot break edit
  mode. `flat()` widgets (the KPI row) render without the card shell.
- First-party widgets register through the same public API in the panel service
  provider, written exactly as an add-on would write them — the dogfooding rule from
  spec 0049.

### 2. Per-staff layout, persisted server-side

**The dashboard is per-staff, not global.** Reasons:

- Permission filtering already personalises the widget set per staff member; a single
  global layout would be a fiction the first time a user can't see a widget in it.
- Roles want different landing pages — support lives in recent orders, merchandising in
  top products and low stock, finance in revenue.
- The cost is one small table; the prototype's copy ("your dashboard") already assumes
  it.

A store-curated default layout (seeding what new staff see) is a plausible follow-on
and deliberately out of scope; registration-time `position()` + `visibleByDefault()`
define the default today.

Persistence is server-side (not localStorage) so the layout follows the staff member
across devices and browsers, and so the server can validate keys and filter by
permission. A new panel-owned table, generic on purpose so future per-staff panel
preferences (table density, collapsed nav) reuse it:

```
staff_preferences            (prefixed like every panel table)
  id
  staff_id      FK → staff, cascadeOnDelete
  key           string            -- 'dashboard'
  value         json
  timestamps
  unique(staff_id, key)
```

The `dashboard` value is `{"range": "30d", "widgets": [{"key": "...", "visible": true}, …]}`
— array order is display order. Merge-on-read reconciles stored config with the live
registry: stored keys no longer registered (or no longer permitted) are dropped; newly
registered widgets are appended in their default order with `visibleByDefault()`.

Routes (authenticated panel group):

- `PUT panel/dashboard/preferences` — full replace of `{range, widgets}`; unknown keys
  ignored, range validated against the enum. Called on every customise-mode change and
  range switch.
- `DELETE panel/dashboard/preferences` — reset to registration defaults.

### 3. The dashboard screen

Rebuilt from the prototype's `Dashboard.vue` onto the panel's page scaffold
(`PageHeader`, the existing `dashboard:main:before/after` zones stay):

- Header: range selector (`today / 7d / 30d / 90d`) + a Customise toggle.
- Grid: `grid-cols-1 lg:grid-cols-2`, `full` widgets spanning both columns. Each
  deferred widget shows a pulsing skeleton sized to its span while loading.
- Customise mode: hint banner; HTML5 drag-by-handle reorder with before/after drop
  indicators (ported from the prototype's `WidgetCard`); × hides; an "Add widget"
  dialog lists hidden widgets with icon/label/description; empty state when everything
  is hidden. Every change persists immediately via the preferences endpoint.
- Range switch: persists, then partial-reloads the deferred widget props.

### 4. Brand chart palette + chart components

New theme tokens anchored on the icon blue (`#4BC7F8` = oklch 0.78 0.13 228), stepped
into the readable band per mode and **validated** with the repo's dataviz palette
checks (lightness band, chroma floor, CVD separation, normal-vision floor, contrast —
all pass in both modes against the panel's actual surfaces):

| Token | Light | Dark | Role |
|---|---|---|---|
| `--color-chart-1` | `oklch(0.60 0.13 228)` `#008ebd` | `oklch(0.66 0.12 228)` `#24a0cc` | brand hue; single-series default (lines, sparklines, bars) |
| `--color-chart-2` | `oklch(0.62 0.13 75)` `#b37903` | `oklch(0.66 0.12 75)` `#bd8630` | categorical slot 2 |
| `--color-chart-3` | `oklch(0.60 0.11 165)` `#2d9570` | `oklch(0.63 0.11 165)` `#399e78` | categorical slot 3 |
| `--color-chart-4` | `oklch(0.55 0.13 300)` `#7e5db1` | `oklch(0.60 0.12 300)` `#8c6ebd` | categorical slot 4 |
| `--color-chart-1-soft` | `oklch(0.95 0.03 228)` | `oklch(0.30 0.05 228)` | area fills under chart-1 |

Categorical slots are assigned in fixed order and never cycled; a fifth segment folds
into "Other". Sage remains the semantic success colour elsewhere in the panel; charts
stop using it.

- **`TimeSeriesChart`** defaults move from sage to `chart-1`/`chart-1-soft` (this also
  recolours the customer order-value chart from spec 0050 — intended).
- **`Sparkline`** — tiny inline area/line for KPI tiles; no axes, no tooltip.
- **`DonutChart`** — segments + centre total + legend with values; segment identity
  is never colour-alone (legend labels + a 2px surface gap between segments).

Both new components are dependency-free inline SVG like `TimeSeriesChart`, exported
from `ui.ts` under the Charts group, mirrored in `@lunarphp/panel`'s `index.js`, types
regenerated — so add-on widgets can build with them.

### 5. First-party widgets

All nine from the prototype, backed by real core data. Revenue is valued in the default
currency via the exchange rate captured at placement (the spec 0050 basis); "orders"
means placed orders throughout. Aggregation fetches minimal columns and buckets in PHP,
consistent with 0050 — revisit with SQL grouping only if profiling demands it.

| Key | Span | Default | Data |
|---|---|---|---|
| `kpis` | full, flat | visible | revenue, order count, AOV, new customers for the range; deltas vs previous period; sparkline series |
| `revenue-chart` | full | visible | bucketed revenue over the range (`TimeSeriesChart`) |
| `recent-orders` | half | visible | latest N placed orders: reference, customer, status, total, placed_at |
| `top-products` | half | visible | top 5 by line revenue in range, with units |
| `channels` | half | visible | revenue by channel (`DonutChart`) |
| `new-vs-repeat` | half | visible | revenue/count split via the `orders.new_customer` flag |
| `customer-groups` | half | hidden | revenue by customer group (`DonutChart`) |
| `low-stock` | half | visible | variants with `stock_available` at or below a threshold |
| `tasks` | full | hidden | counts: pending orders, draft products, out-of-stock actives |

- Order/product rows link to their panel pages where a section exists (Products, spec
  0057); order references stay unlinked until an Orders section exists — the 0049
  precedent.
- The low-stock threshold is a config value (`lunar.panel.dashboard.low_stock_threshold`,
  default 10) — config is for values. When per-variant reorder thresholds land (the
  spec 0038 follow-on), the widget switches to them.

### Testing

- Pest: registry resolution (ordering, permission filtering, section + `Panel::widget()`
  registration), preference merge semantics (dropped keys, appended new widgets,
  visibility), endpoint validation + reset, per-widget `data()` correctness with
  factories (currency conversion, previous-period deltas, bucket boundaries), Inertia
  prop assertions including deferred groups.
- Fixture add-on registers a widget (with permission + anchored position) proving the
  extension point without touching panel source.
- Vitest: grid ordering/reorder logic, skeleton + deferred rendering, customise-mode
  interactions, `Sparkline`/`DonutChart` rendering.
- PHPStan + Pint per the pipeline.

## Alternatives considered

- **localStorage layout** (as the prototype's store does) — rejected: doesn't follow
  staff across devices, can't be validated or permission-filtered server-side.
- **One global layout** — rejected above; a curated org-wide *default* is a follow-on,
  not a replacement for per-staff.
- **Widgets as slot-zone entries** — rejected: slots are render-only. Widgets need
  identity (reorder/hide prefs), metadata (customise dialog), and a server data
  contract; that's a typed extension point, the same reasoning that made `PageAction`
  a class rather than a slot.
- **Free-form grid library** (gridstack, vue-grid-layout) — rejected: x/y/w/h layouts
  bring a dependency, collision logic, and a responsive-breakpoint matrix. The
  prototype's ordered flow list + `full|half` span is responsive by construction and
  covers the real need.
- **Per-widget fetch endpoints** — rejected: Inertia v3 deferred props already give
  parallel post-paint loading without inventing an API layer.
- **A charting library** — rejected in spec 0050; unchanged.
- **`staff.dashboard_config` column in core** — rejected: panel-specific state doesn't
  belong on the core staff table; the panel already owns tables (`edit_drafts`).

## Migration impact

- Database: one new panel migration, `staff_preferences` (panel is new in v2; its
  own migrations are its baseline).
- Public contract surface: additive — `Widget`/`WidgetSpan`/`DashboardRange`,
  `Section::widgets()`, `Panel::widget()`, the `widgets` + deferred `widget:{key}`
  props, the preferences endpoints, `Sparkline`/`DonutChart` npm exports, the
  `--color-chart-*` tokens.
- v1.x upgrade path: not applicable (panel is new in v2).
- Translations: widget labels/descriptions, dashboard UI strings, task labels — all 16
  locales.
- Filament admin: none.

## Open questions

- Should a store-curated default layout (what new staff see) ship later as an admin
  setting? Deferred; registration defaults serve for now.
- Widget aggregate caching for large stores (e.g. `Cache::remember` keyed by
  range + day) — deferred until profiling shows need.
- Is 10 the right `low_stock_threshold` default before the 0038 follow-on lands?

## References

- [[0049-inertia-panel]] — extension architecture, dogfooding rule, page scaffold.
- [[0050-panel-order-history-chart]] — `TimeSeriesChart`, currency basis, no-library
  decision.
- Design prototype: `/Users/glenn/GitHub/lunarphp/lunar-v2-ui` (`src/pages/Dashboard.vue`,
  `src/components/dashboard/`, `src/composables/useDashboard.js`).
- Brand colours: `packages/panel/public/favicons/favicon.svg` (`#4BC7F8`, `#122036`).
- Palette validated with the repo dataviz checks against panel surfaces
  (light `#ffffff`, dark `#16181c`), both modes all-pass.

## Implementation plan

- [x] Slice 1 — chart palette tokens; `Sparkline` + `DonutChart` on the public surface
  (ui.ts, `index.js`, regenerated types, vitest); `TimeSeriesChart` recoloured.
- [x] Slice 2 — widget extension point: `Widget`, `WidgetSpan`, `DashboardRange`,
  `WidgetRegistry`, `Section::widgets()` + `Panel::widget()`, controller props with
  per-widget deferred data; dashboard grid rendering with skeletons; fixture add-on
  widget coverage.
- [x] Slice 3 — preferences: `staff_preferences` migration + model, merge-on-read,
  PUT/DELETE endpoints; customise mode (drag reorder, hide, add dialog, empty state,
  range selector persistence).
- [x] Slice 4 — widgets: `kpis` (KPI tiles with deltas + sparklines) and `revenue-chart`.
- [x] Slice 5 — widgets: `recent-orders`, `top-products`, `channels`, `new-vs-repeat`,
  `customer-groups`.
- [x] Slice 6 — widgets: `low-stock` (+ threshold config), `tasks`; example add-on
  widget; translations swept across the 16 locales.
