# 0050 — Panel order-value chart and public charting component

- Status: implemented
- Author: Glenn (via pairing session)
- Created: 2026-07-16
- TODO item: Panel order-value chart on the customer edit page + chart component on the add-on surface

## Problem

The customer edit page now shows lifetime stats (total spend, order count, average
order, latest order) and an order-history list, but no temporal view. An admin
cannot see *when* a customer's spend happened — whether they are a lapsed customer,
a seasonal buyer, or growing — without reading the order list row by row.

Separately, the panel has no charting primitive at all. Any page or add-on that
wants a chart today would have to hand-roll SVG or bundle its own charting library,
which would neither match the panel's design language nor stay consistent across
add-ons.

## Proposal

Two parts: a generic chart component on the panel's public component surface, and
an order-value-over-time card on the customer edit page built with it.

### 1. `TimeSeriesChart` component

A dependency-free inline-SVG area/line chart in
`packages/panel/resources/js/components/TimeSeriesChart.vue`. No charting library:
the need is one numeric series over ordered time buckets, which is ~200 lines of
SVG and keeps the public surface free of a new npm dependency.

```ts
export interface ChartPoint {
    label: string;      // bucket label, e.g. "Aug 25"
    value: number;      // numeric value in major units, drives the geometry
    display?: string;   // preformatted value for tooltip/ticks, e.g. "£125.50"
}

defineProps<{
    points: ChartPoint[];
    height?: number;                          // px, default 180
    formatValue?: (value: number) => string;  // y-tick fallback when display is absent
    ariaLabel: string;                        // required; the SVG renders role="img"
}>();
```

Behaviour:

- Area fill + line stroke using the panel theme tokens (sage line, soft fill,
  hairline gridlines, ink-500 labels) so it reads as part of the existing design
  system; visual detailing follows the repo's dataviz guidance during
  implementation.
- Hover: nearest-point dot plus a tooltip showing `label` and `display`.
- A handful of y-axis gridlines with formatted tick labels; x labels thinned to
  fit the available width.
- Zero-value series still renders (flat baseline), empty `points` renders nothing —
  callers decide their empty state.
- Accessible: `role="img"` with the required `ariaLabel`; pointer interaction is
  progressive enhancement only.

Public surface: exported from `resources/js/ui.ts` under a new "Charts" group,
mirrored in the `@lunarphp/panel` package's `index.js` runtime fallback list, and
its type lands in `dist/ui.d.ts` via the existing `npm run build:types`. Add-ons
then import it like any other panel component. `KpiCard` remains the primitive for
single-number tiles; `TimeSeriesChart` complements it.

### 2. Order value over time on the customer edit page

A full-width card at the top of the edit page's main column (above Personal
details), titled from a new `customers.chart_title` key, containing the chart and
a range switcher.

Server side, `CustomerEditController::edit()` gains an `orderChart` prop:

```php
'orderChart' => [
    'range' => '12m',
    'buckets' => [
        ['label' => 'Aug 25', 'value' => 125.50, 'display' => '£125.50'],
        // ... one entry per bucket, zero-filled
    ],
],
```

- Buckets cover placed orders only, valued in the default currency via the
  exchange rate captured at placement — the same basis as the lifetime stats, so
  the chart and the stats card always agree.
- Ranges via a validated `chart_range` query param:

  | range | window          | bucket    | points |
  |-------|-----------------|-----------|--------|
  | `12m` | last 12 months  | month     | 12     |
  | `3y`  | last 3 years    | quarter   | 12     |
  | `5y`  | last 5 years    | quarter   | 20     |
  | `10y` | last 10 years   | year      | 10     |

  `12m` is the default; unknown values fall back to it.
- Bucketing happens in PHP over the customer's placed orders in the window
  (`placed_at`, `total`, `exchange_rate` only). A single customer's order count is
  small; this avoids cross-database date-formatting SQL. Revisit with SQL grouping
  only if it ever shows up in profiles.
- Client side, the range switcher performs a partial Inertia reload
  (`only: ['orderChart']`, preserve state/scroll) with the `chart_range` param, so
  zooming never re-fetches the rest of the page.

Translations: chart title, the four range labels, and the chart empty state, added
across all 16 locales.

## Alternatives considered

- **Bundle a charting library (chart.js / ApexCharts / unovis).** Rejected: adds a
  runtime dependency to the public add-on surface and the panel bundle, brings its
  own theming system that fights the panel tokens, and we need a fraction of its
  features.
- **Reinstate the prototype's top stat strip.** Rejected earlier in review — it
  duplicated the sidebar Lifetime stats. The chart answers the question the strip
  could not (when), without duplicating totals.
- **Bucket client-side from the `orders` prop.** Rejected: that prop is capped at
  the latest 25 orders for the history tab; the chart needs the full window.
- **Do nothing.** Leaves the "when" question unanswered and leaves add-ons to
  invent their own charts.

## Migration impact

- Database migrations: none.
- Public contract surface: additive only — new `TimeSeriesChart` export on the
  add-on surface; new `orderChart` page prop and `chart_range` query param.
- v1.x upgrade path: not applicable (panel is new in v2).
- Translations: new keys mirrored across the 16 locales.
- Filament admin: none.

## Open questions

- Range set: is `12m / 3y / 5y / 10y` the right ladder, or should an `all` range
  (yearly buckets from first order) be included?
- Does the card belong on a future Orders dashboard too? Nothing blocks it — the
  component is generic — but placement there is out of scope here.

## References

- [[0049-inertia-panel]] — panel foundation, extension surface, and the customers
  section this builds on.
- Panel CLAUDE.md — `ui.ts` as the source of truth for the add-on surface.

## Implementation plan

- [x] Slice 1 — `TimeSeriesChart` component with vitest coverage; export via
  `ui.ts`, mirror in `@lunarphp/panel` `index.js`, regenerate types.
- [x] Slice 2 — `orderChart` data on `CustomerEditController` (range param,
  zero-filled buckets, default-currency conversion) with Pest coverage; edit-page
  card with range switcher and partial reload.
- [x] Slice 3 — translations across the 16 locales; update spec 0049's screen
  description; example add-on gains a small chart usage to prove the surface.
