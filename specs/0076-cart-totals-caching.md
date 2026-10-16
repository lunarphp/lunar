# 0076 — Cart totals caching in the database

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-09-09
- TODO item: Cart totals caching in the database — additive performance optimisation

## Problem

`Cart::calculate()` runs the full calculation pipeline every time a request touches a cart, and nothing survives the request.

- **Every page pays for the pipeline.** `CartSession::current()` calculates by default, so the mini-cart on every storefront page runs `CalculateLines` (an eleven-relation `loadMissing`, then a `Pricing` resolution per line), `ApplyDiscounts` (fetch the eligible discount set, run every type), `CalculateTax` (a tax breakdown per line plus shipping), and `ApplyShipping` / `CalculateTax` each resolving the shipping option through the shipping-modifier pipeline. A shipping modifier that quotes rates from a carrier API runs on every render, not once per cart change.
- **The only memo is per request.** `Models\Concerns\CachesProperties` parks the computed `PriceValue` properties in Blink so a second `calculate()` in the same request is free (`isCalculated()`). The next request starts from nothing. Under Octane the Blink store is flushed per request, so there is no accidental cross-request reuse either.
- **Nothing about a cart's value is queryable.** `carts` and `cart_lines` carry no money columns. A future panel Carts section, an abandoned-cart value KPI, or a "carts over 100 GBP" report would have to load and recalculate every cart. `orders` already stores `sub_total`, `discount_total`, `shipping_total`, `tax_total`, `total` and the three breakdowns; carts have no equivalent.

The v2 prototype (the `lunar/sales` package in the `lunar2` repo) shipped a working version of this: totals persisted to `carts` / `cart_lines` alongside a `calculated_at` timestamp, served from the row while within a configurable TTL, and invalidated by nulling `calculated_at` from every mutation verb. This spec brings that design to `lunarphp/core`, adapted to v2's `PriceValue` property surface and the action / verb conventions of specs 0016 and 0029, and replaces the timestamp-based invalidation with a revision counter so concurrent requests cannot stamp a stale snapshot fresh.

## Proposal

Persist the pipeline's output to the cart and line rows after each calculation, and serve the next `calculate()` from those rows when they are still fresh. Fresh means: a snapshot exists, nothing on the cart has changed since it was written, and it is younger than a configurable TTL. Checkout and payment always force a recalculation. The public surface a storefront reads (`$cart->total->value`, `$line->unitPrice`, `$cart->taxBreakdown`, …) is unchanged; a hydrated cart is indistinguishable from a freshly calculated one for everything it renders.

### A. Schema (baseline edits)

v2 is in alpha, so the columns are born on the existing `create_carts_table` / `create_cart_lines_table` baseline migrations rather than added in change migrations. All are nullable: `NULL` means "not calculated". Names mirror `orders` / `order_lines`, not the in-memory property names (`tax_total`, not `tax_amount`).

`carts`:

| Column | Type | Source property |
| --- | --- | --- |
| `sub_total` | `unsignedBigInteger` nullable | `subTotal` |
| `sub_total_discounted` | `unsignedBigInteger` nullable | `subTotalDiscounted` |
| `discount_total` | `unsignedBigInteger` nullable | `discountTotal` |
| `shipping_sub_total` | `unsignedBigInteger` nullable | `shippingSubTotal` |
| `shipping_tax_total` | `unsignedBigInteger` nullable | `shippingTaxTotal` |
| `shipping_total` | `unsignedBigInteger` nullable | `shippingTotal` |
| `tax_total` | `unsignedBigInteger` nullable | `taxTotal` |
| `total` | `unsignedBigInteger` nullable | `total` |
| `tax_breakdown` | `jsonb` nullable | `taxBreakdown` |
| `shipping_breakdown` | `jsonb` nullable | `shippingBreakdown` |
| `discount_breakdown` | `jsonb` nullable | `discountBreakdown` |
| `free_items` | `jsonb` nullable | `freeItems` |
| `revision` | `unsignedInteger` default `0` | — (bumped by every change to the cart) |
| `calculated_revision` | `unsignedInteger` nullable | — (the `revision` the snapshot was computed at) |
| `calculated_at` | `timestamp` nullable | — (when the snapshot was computed) |

`cart_lines`:

| Column | Type | Source property |
| --- | --- | --- |
| `unit_price` | `unsignedBigInteger` nullable | `unitPrice` |
| `unit_price_incl_tax` | `unsignedBigInteger` nullable | `unitPriceInclTax` |
| `sub_total` | `unsignedBigInteger` nullable | `subTotal` |
| `sub_total_discounted` | `unsignedBigInteger` nullable | `subTotalDiscounted` |
| `discount_total` | `unsignedBigInteger` nullable | `discountTotal` |
| `tax_total` | `unsignedBigInteger` nullable | `taxAmount` |
| `total` | `unsignedBigInteger` nullable | `total` |
| `tax_breakdown` | `jsonb` nullable | `taxBreakdown` |
| `promotion_description` | `string` nullable | `promotionDescription` |

JSON shapes:

- `tax_breakdown` / `shipping_breakdown` — the existing `Casts\TaxBreakdown` and `Casts\ShippingBreakdown` serialisations, reused as-is (`'tax_breakdown' => Casts\TaxBreakdown::class` etc. on `Cart` and `CartLine`).
- `discount_breakdown` — `[{ "discount_id": 3, "total": 500, "lines": [{ "id": 12, "qty": 2 }] }]`. Same shape as the order column, but the line ids are cart-line ids, so it is cast as a plain `array` on `Cart` and rebuilt into `ValueObjects\Cart\DiscountBreakdown` objects by the hydrator (section C). The existing `Casts\DiscountBreakdown` is order-specific (it resolves `OrderLine`s) and is not reused.
- `free_items` — `[{ "type": "product_variant", "id": 41 }]`, the morph reference of each purchasable in `$cart->freeItems`. `BuyXGetY` pushes the reward `ProductVariant` itself onto this collection, so a morph reference is what is needed to rebuild it.

**Property shadowing.** `Cart` and `CartLine` declare a public `?PriceValue $total`. A declared property wins over Eloquent's `__get`, so `$cart->total` keeps returning the `PriceValue` and the new column is only reachable through `getAttribute('total')` / `getRawOriginal('total')` / `toArray()`. This is what keeps the read surface stable, and it is the only name that collides (`subTotal` vs `sub_total` and the rest differ). The hydrator and persister read and write the columns through the attribute API, never the property. Array access is the one path `__get` does not cover: `$line['total']`, `Collection::pluck('total')` and `data_get()` read models through `ArrayAccess`, which resolves attributes first, so `CachesProperties` overrides `offsetExists()` / `offsetGet()` to prefer a declared cachable property (the `Calculate` stage itself does `$cart->lines->pluck('total')`). Spec 0012 chose `PriceValue` properties for the cart deliberately; this spec does not revisit that.

**Serialisation.** The new attributes appear in `toArray()` / JSON for `Cart` and `CartLine` as raw integers and arrays, alongside the properties a consumer already maps by hand. Additive; nothing is hidden.

### B. Freshness and invalidation

Two integer columns describe the cart's state: `revision` counts changes to the cart, and `calculated_revision` records which revision the persisted snapshot was computed at. A cart's snapshot is fresh when:

```
calculated_revision = revision
AND calculated_at >= now() - ttl
```

The first clause says nothing has changed since the snapshot was written. The second bounds staleness from inputs the cart cannot see. `ttl` is `config('lunar.cart.totals.ttl')`, in seconds, default `300`. The TTL exists because the cart cannot observe every input to its own totals: a price edit, a discount reaching its end date, a tax-zone change, or a carrier repricing all change the answer without touching the cart. Five minutes bounds how stale a displayed total can be; checkout never reads the snapshot at all (section E). Setting `ttl` to `0` means the snapshot is never served; it is still written, so the reporting columns stay populated. Consumers that want no writes bind their own `CalculatesCart` (section C).

Everything that *does* touch the cart invalidates the snapshot by bumping `revision`. The snapshot columns are left in place: the last computed figures stay readable for reporting and `calculated_at` still says when they were computed, they are simply no longer served. Biased toward firing, as spec 0043: a needless bump costs one pipeline run, a missed one serves a wrong total.

| Change | Mechanism |
| --- | --- |
| Any `Cart` attribute outside the snapshot columns changes (`currency_id`, `channel_id`, `region_id`, `customer_id`, `user_id`, `coupon_code`, `tax_zone_id`, `meta`, …) | `Cart::updating` hook: if `getDirty()` minus the snapshot and revision columns is non-empty, set `revision` to the raw expression `revision + 1` in the same save |
| `CartLine` saved or deleted (quantity, meta, purchasable, create, remove) | `CartLineObserver::saved` / `deleted` -> `$line->cart->invalidateTotals()` |
| `CartAddress` saved or deleted (address fields, `shipping_option`, `tax_identifier`) | `CartAddressObserver::saved` / `deleted` -> `$address->cart->invalidateTotals()` (new observer, registered in `LunarServiceProvider`) |
| `Cart::clear()` (query-builder delete, no model events) | explicit `$this->invalidateTotals()` after the delete |
| `AddAddress` (query-builder delete of the previous address, then a model save) | the save fires the observer; no extra call needed |
| `UpdateCartLine` | today a `CartLine::whereId()->update()` that bypasses observers, `LogsActivity`, and the purchasable check in `CartLineObserver::updating`; switched to load-and-save so the observer fires |
| `MergeCart` | creates / updates lines through the model layer; the observer fires |
| Anything outside the verbs | `$cart->invalidateTotals()` is public for consumers who write cart rows directly |

`Cart::invalidateTotals(): static` is `incrementQuietly('revision')` — an atomic `SET revision = revision + 1` that fires no model events and logs no activity. `Cart::totalsAreFresh(): bool` exposes the freshness rule.

**Every bump is a SQL-side increment.** A PHP-side `$cart->revision + 1` is a read-modify-write: two requests that each loaded revision 3 would both write 4, a persister holding 4 would pass the guard, and the counter could even move backwards. `incrementQuietly()` handles the observer paths; the `updating` hook assigns a raw `revision + 1` expression, which Eloquent writes verbatim. Because that expression is opaque in memory, a `saved` hook reads the produced value back (one primary-key lookup, only when the hook fired) so the persister always guards on an integer. A cart created in the current request carries `revision = 0` as a model attribute default, matching the column default, so a persist before any reload guards on the value the row holds.

**Worked example.** One cart through a session, snapshot columns other than `total` omitted:

| Step | revision | calculated_revision | calculated_at | total | fresh? |
| --- | --- | --- | --- | --- | --- |
| Cart created | 0 | NULL | NULL | NULL | no |
| Line added, verb recalculates | 1 | 1 | 10:00:05 | 2400 | yes |
| Mini-cart render two minutes later (hydrated, no write) | 1 | 1 | 10:00:05 | 2400 | yes |
| Quantity changed (line observer bumps) | 2 | 1 | 10:00:05 | 2400 | no |
| Verb recalculates | 2 | 2 | 10:02:11 | 4800 | yes |
| Coupon applied (`updating` hook bumps), verb recalculates | 3 | 3 | 10:03:40 | 4320 | yes |
| Page view ten minutes later: TTL expired, pipeline re-runs, revision unchanged | 3 | 3 | 10:14:02 | 4320 | yes |
| Request A adds a line while request B is mid-pipeline at revision 3 | 4 | 3 | 10:14:02 | 4320 | no |
| B persists `WHERE revision = 3` — zero rows, skipped | 4 | 3 | 10:14:02 | 4320 | no |
| A's own recalculate lands | 4 | 4 | 10:14:10 | 6720 | yes |

"Carts holding a stale snapshot" is `calculated_revision <> revision OR calculated_revision IS NULL`, no timestamp arithmetic.

**Self-writes during the pipeline.** The pipeline itself can write lines: `BuyXGetY::processAutomaticRewards()` creates and saves reward lines while the cart is being calculated. Those writes are part of the calculation, not a change to it, so they must not invalidate the snapshot about to be written. `CalculateCart` marks the cart instance as calculating for the duration of the pipeline (`Cart::isCalculating()`, an instance flag set in a `try` / `finally`), and the line observer skips invalidation when the line's parent is that instance and is calculating. For the observer to see the same instance, `Cart::lines()` gains `->chaperone()`, so lines hydrated through the relation and lines made via `$cart->lines()->make()` / `create()` carry the parent back. A line whose `cart` relation resolves to a different instance invalidates as normal — the false-positive direction.

**Write guard.** Persisting is a single query-builder `UPDATE` (no `save()`, so no events, no activity log, no `updated_at` bump) guarded on the `revision` the cart was loaded with, which is also the value written to `calculated_revision`:

```sql
UPDATE carts SET sub_total = ?, …, calculated_revision = ?, calculated_at = ? WHERE id = ? AND revision = ?
```

Zero rows affected means another request changed the cart while this one was calculating; the snapshot is skipped, the in-memory totals stay valid for this request, and the next request recalculates. (MySQL reports changed rows rather than matched rows, so a zero result is confirmed by re-reading `revision` before it is treated as a failed guard.) Line rows are written with one `UPDATE ... WHERE id = ?` per line inside the same transaction, only when the cart guard passed — not an `upsert`, which would resurrect a line another request removed between the guard and the write. The guard is exact: every change is a distinct integer, so there is no timing window, and `updated_at` keeps its ordinary meaning for pruning and activity.

**Snapshot completeness.** A line the pipeline itself adds (a `BuyXGetY` automatic reward) is saved during the run but is not in `$cart->lines` for that run, so the snapshot written afterwards has no totals for it. `CalculateCart` treats a snapshot that does not cover every loaded line (`cart_lines.total IS NULL`) as not servable and runs the pipeline, which fills the line in; from the next request the snapshot is complete and served. Serving it early would render the reward line without a price.

### C. Actions and entry points

Per spec 0029 the operation lives in an action; the model verb is a one-line delegate.

- `Actions/Carts/CalculateCart` implements `Contracts\Actions\Carts\CalculatesCart` — `execute(Cart $cart, bool $force = false): Cart`. Owns the decision: in-memory `isCalculated()` and not forced -> return; snapshot fresh and not forced -> hydrate; otherwise run `lunar.cart.pipelines.cart` through `Pipeline`, `cacheProperties()`, persist. `Cart::calculate()` becomes `return app(CalculatesCart::class)->execute($this, $force);` and `recalculate()` is unchanged. Binding a different `CalculatesCart` is how a consumer replaces the caching policy wholesale.
- `Actions/Carts/HydrateCartTotals` implements `Contracts\Actions\Carts\HydratesCartTotals` — `execute(Cart $cart): Cart`. Loads `currency` and `lines` if missing, builds every `PriceValue` property on the cart and its lines from the columns, `TaxBreakdown` / `ShippingBreakdown` through the casts, `discountBreakdown` from the JSON (one `Discount::whereIn` for the ids; lines matched from `$cart->lines` by id; a line id that no longer exists drops that entry), `discounts` as `discountBreakdown->map(fn ($b) => $b->discount->getType())` so `DiscountManager::apply()` and `CreateOrder` see the same collection a pipeline run produces, `freeItems` from the morph references (one query per morph type), then `cacheProperties()`. Lossless with respect to `GenerateFingerprint`, which reads `subTotal->value` per line: a hydrated cart fingerprints identically to a calculated one, so `currentDraftOrder()` keeps matching.
- `Actions/Carts/PersistCartTotals` implements `Contracts\Actions\Carts\PersistsCartTotals` — `execute(Cart $cart): bool`. The guarded `UPDATE` plus the per-line updates in one transaction; syncs the written values into `attributes` / `original` on the in-memory models; returns whether the guard passed. A breakdown whose `lines` hold `CartLine` models rather than `DiscountBreakdownLine` objects (a consumer discount type) is stored at the line's quantity.

All three register in `ActionServiceProvider::$actions`. `CalculateCart` injects the other two plus `Pipeline`; `PersistCartTotals` injects the connection resolver for its transaction; none hold state, so they are ordinary (non-scoped) bindings and `ServiceLifetimesTest` is unaffected. `Cart::isCalculating()` is a per-instance flag on the model (`setCalculating()` / `isCalculating()`), not service state. `CalculateCart` also memoises every line's properties after the pipeline (`CalculateLines` memoised them before tax ran), so a re-fetched line in the same request restores post-tax figures; `CartSessionManager::setCurrency()` now refreshes the cart instead of unsetting relations, because the full memo would otherwise satisfy `isCalculated()` and reprice in the old currency.

**Not carried by the snapshot.** `promotions` (declared on `Cart`, populated by nothing in core), `shippingOptionOverride` and `shippingEstimateMeta` (inputs, not outputs; `CartSession::estimateShipping()` already forces after setting them), and the computed properties on `CartAddress` (`shippingOption`, `shippingSubTotal`, `shippingTaxTotal`, `shippingTotal`, `taxBreakdown`). The address properties are consumed only by `CreateShippingLine`, which runs after a forced recalculation. Documented as "populated by a pipeline run".

### D. Config

`config/cart.php` gains:

```php
'totals' => [
    // Seconds a persisted totals snapshot is served before the pipeline
    // re-runs. 0 never serves the snapshot; it is still written.
    'ttl' => 300,
],
```

The panel / Filament settings surfaces do not expose it; it is a deploy-time value.

### E. Checkout and payment always force

- `Cart::createOrder()` already does `refresh()->recalculate()`; `CreateOrderLines` and `CreateShippingLine` call `recalculate()`; `FillOrderFromCart` calls `calculate()` and hits the in-memory memo from the forced run earlier in the same request. Unchanged.
- Payment drivers read cart totals in six places: `StripePaymentType::assertIntentMatchesTotal()`, `PaypalPaymentType::assertOrderMatchesTotal()`, `StripeManager::updateIntent()`, `Paypal::createOrder()` (`$cart->total ? $cart : $cart->calculate()`), `ProcessStripeWebhook`, and `ProcessPaypalWebhook`. Each moves to `recalculate()`. The amount guard exists to catch a total that changed after the intent was created; a guard that compares the intent to a snapshot of the same age would pass exactly when it should fail. The payment-driver paragraph in `CLAUDE.md` gains the sentence "read cart totals through `recalculate()`, never `calculate()`, so the amount guard compares against a fresh total".
- `StripeManager::createIntent()` reads `$cart->total` without calculating and relies on the caller; unchanged.

### F. Activity log

`Cart` and `CartLine` use `LogsActivity` with `logAll()->logOnlyDirty()`. The persister bypasses `save()`, and `invalidateTotals()` is quiet, but the `updating` hook bumps `revision` inside an ordinary save and would log it. Both models add the snapshot columns, `revision`, `calculated_revision` and `calculated_at` to `getDefaultLogExcept()`.

### G. Tests

`tests/core/Unit/Actions/Carts/CalculateCartTest.php`, `HydrateCartTotalsTest.php`, `PersistCartTotalsTest.php`, plus additions to `CartTest`, the action tests for each mutation verb, and the driver suites:

- Calculating persists every column on the cart and its lines, and sets `calculated_revision` to the current `revision` and `calculated_at` to now.
- A fresh reload followed by `calculate()` does not run the pipeline (assert on a spy pipeline stage) and yields the same `total`, `subTotal`, line `unitPrice`, `taxBreakdown` amounts, `shippingBreakdown` items, `discountBreakdown` (discount, lines, quantities, price), `discounts`, `freeItems`, and `fingerprint()` as the calculated cart.
- `recalculate()` / `force: true` runs the pipeline while fresh.
- `totalsAreFresh()` is false with no snapshot, true inside the TTL, false past it, honours a custom `ttl`, and `ttl = 0` never serves the snapshot but still writes it.
- Each of `add`, `updateLine`, `remove`, `clear`, `addAddress`, `setShippingOption`, `setTaxZone`, `associate`, `setCustomer`, a `coupon_code` update, a `currency_id` update, and `MergeCart` bumps `revision` exactly once per write and leaves `calculated_revision` behind it, so `totalsAreFresh()` is false until the verb's own `recalculate()` lands.
- A TTL expiry recalculation rewrites the snapshot without moving `revision`.
- A cart modified between load and persist (bump `revision` directly) is not overwritten, the persister reports the skip, and the in-memory totals are still correct.
- Two concurrent bumps (an `incrementQuietly()` racing a `Cart::updating` save) leave `revision` two higher, never one — the read-modify-write regression test.
- A `BuyXGetY` automatic reward saved during the pipeline does not prevent the snapshot being written.
- `UpdateCartLine` fires `CartLineObserver::updating` (the purchasable check) — a regression test for the switch to a model save.
- Stripe and PayPal amount guards reject a stale snapshot whose total differs from the pipeline result.
- `ArchitectureTest` covers the new actions automatically (contract, `execute()`, no facades).

## Alternatives considered

- **Do nothing.** Keep the per-request Blink memo. Every page render pays the full pipeline, including any external shipping-rate call, and cart value stays unqueryable. Rejected — this is the TODO item.
- **A Laravel cache store (Redis) keyed by cart id.** No schema change, but the `PriceValue` / breakdown / `Discount` graph has to be serialised anyway, eviction is not invalidation, cart value is still not queryable, and the cart row is loaded regardless. The database row is already the natural home and the prototype proved it. Rejected.
- **One JSON `totals` column.** Fewer columns, but not queryable and no better than the discrete columns for serialisation effort. Mirroring `orders` gives the reporting use case for free. Rejected.
- **Fingerprint-based freshness instead of TTL plus invalidation.** Recompute the content hash on load and compare to a stored one. The fingerprint covers cart content only, not prices, discounts, tax or shipping rules, so a TTL is still needed; and computing it needs the lines loaded anyway. Rejected as the freshness mechanism; the fingerprint keeps its draft-order-matching role.
- **`updated_at` as the write guard, `calculated_at = NULL` as the invalidation signal.** The prototype's shape and one column fewer. Second-precision timestamps leave a window in which a rapid sequence of quantity changes racing a mini-cart render stamps a stale snapshot fresh, and the column has to carry two meanings (invalidation must move it, persisting must not). Rejected for the revision counter.
- **A regenerated token instead of a counter.** A `totals_version` ULID rewritten on every change has no read-modify-write hazard at all, since the new value does not derive from the old. Costs a 26-character string column and gives up ordering. Kept as the fallback if the SQL-side increment rule proves awkward in practice; the counter is conventional (`lock_version`), smaller, and doubles as a weak ETag for a future storefront API.
- **Persist from inside the pipeline (a final `PersistTotals` stage).** Puts a write in a list consumers reorder and replace, and a consumer who drops the stage silently loses caching. The action owns the write so the pipeline stays pure. Rejected.
- **Make cart totals attributes rather than properties, like `Order`.** Would remove the `total` shadowing and let the row be the single source. It is a breaking change to every `$cart->total->value` read and reverses a spec 0012 decision; out of scope for an additive optimisation.

## Migration impact

- **Database migrations:** baseline edits to `create_carts_table` and `create_cart_lines_table` (v2 alpha, flat baseline). For v1 databases the upgrade package adds one data migration, `add_cart_totals_columns`, that applies the same columns guarded on `hasColumn` (the baseline is marked run by the ledger rewrite, so the schema delta is applied in the upgrade step, as spec 0039 did for `region_id`). No backfill: `revision` defaults to `0`, `calculated_revision` is `NULL`, so every upgraded cart reads as "not calculated" and the next `calculate()` fills it.
- **Breaking changes to the public contract surface:** none. `Cart::calculate(bool $force = false): Cart` keeps its signature; `recalculate()` unchanged. New surface: `Cart::totalsAreFresh()`, `Cart::invalidateTotals()`, `Cart::isCalculating()` / `setCalculating()`, `Cart::totalsColumns()`, `CartLine::totalsColumns()`, `Cart::$revision`, `Cart::$calculated_revision`, `Cart::$calculated_at`, the three action contracts, `lunar.cart.totals.ttl`. `Cart::$subTotalDiscounted` and `CartLine::$subTotalDiscounted` join the per-request memo (`cachableProperties`). Array access on `Cart` / `CartLine` / `CartAddress` now prefers a declared cachable property over an attribute of the same name (section A). Behavioural note for the upgrade guide: `calculate()` may now return persisted totals up to `ttl` seconds old; code that needs a guaranteed-fresh total uses `recalculate()`. No Rector rule (nothing renamed).
- **Upgrade path for v1.x consumers:** the data migration above; the upgrade guide gains the `calculate()` / `recalculate()` note and the `UpdateCartLine` behaviour change (it now fires model events and the purchasable check, as `add()` always has).
- **Translation / locale impact:** none.
- **Filament / admin impact:** none — neither admin package renders carts. The panel has no Carts section today; when one lands it can sort and aggregate on `carts.total` directly.
- **Docs:** follow-up PR in `lunarphp/docs` porting the prototype's "Persisted totals and caching" and "Automatic invalidation" sections to the v2 carts page, with `recalculate()` called out for checkout and payment code.

## Open questions

- ~~Revision counter vs. `updated_at` guard.~~ **Resolved (maintainer): revision counter.** The `updated_at` guard is second-precision, so a rapid sequence of quantity changes racing a mini-cart render could stamp a stale snapshot fresh, and it borrows a column that has to mean two things at once. A `revision` / `calculated_revision` pair is exact, independent of timestamps, and makes the write guard and the freshness check the same fact (section B). Shipped now while the baseline is open rather than retrofitted after release.
- **External invalidation via spec 0043 events.** A `DiscountCreated` / `DiscountUpdated` / `DiscountDeleted` listener could bump `revision` on every cart with a snapshot (`UPDATE carts SET revision = revision + 1 WHERE calculated_revision IS NOT NULL`, one statement, rare admin action), and `ProductInvalidated` could target carts holding that product's variants (a subquery on `cart_lines`). Both are bounded but touch a table that grows with abandoned carts. Proposal: out of scope for this spec; the TTL covers it; record under Ideas as a follow-on so the fan-out cost is weighed on its own.
- **Skip pricing eager loads on a hit.** `lunar.cart.eager_load` loads `lines.purchasable.prices.*` for every `CartSession::current()`, which a snapshot hit does not need. A two-step load (cart, check freshness, then load only display relations) is a further win. Proposal: follow-on, once the hit rate is known.

## References

- Prototype: `/Users/glenn/Herd/lunar2/packages/lunar/packages/sales` — `Calculators\CartCalculator`, `Models\Cart::totalsAreFresh()` / `persistTotals()` / `invalidateTotals()`, `tests/Unit/Managers/CartCalculatorTest.php`, `docs/2.x/sales/carts.mdx` (section "Persisted Totals and Caching").
- [[0012-price-data-type-refactor]] — why cart totals are `PriceValue` properties, not attributes.
- [[0016-service-layer-di]], [[0029-entry-point-conventions]] — action / verb shape.
- [[0043-cache-invalidation-and-events]] — the bias-toward-firing invalidation stance; `CartLinesUpdated` deferral.
- [[0064-scoped-service-lifetimes]] — why the calculating marker is instance state, not service state.
- [[0070-first-party-payment-drivers]] — the amount guard this spec hardens.

## Implementation plan

- [x] Slice 1 — Schema and model surface: baseline columns on `carts` / `cart_lines`; casts; `revision`, `calculated_revision`, `calculated_at`; `totalsAreFresh()`, `invalidateTotals()`, `isCalculating()`; activity-log excludes; `lines()->chaperone()`; `lunar.cart.totals.ttl`; unit tests for freshness and invalidation verbs.
- [x] Slice 2 — `CalculateCart`, `HydrateCartTotals`, `PersistCartTotals` actions and contracts; `Cart::calculate()` delegates; persist / hydrate / guard / self-write tests; hydration-fidelity test against the pipeline.
- [x] Slice 3 — Invalidation wiring: `CartLineObserver` / new `CartAddressObserver`, `Cart::updating` hook, `UpdateCartLine` model save, `clear()`; per-verb tests.
- [x] Slice 4 — Payment drivers read through `recalculate()`; `CLAUDE.md` payment-driver note; Stripe / PayPal guard tests.
- [x] Slice 5 — Upgrade data migration `add_cart_totals_columns`.
- [x] Follow-up — `lunarphp/docs` PR (lunarphp/docs#50).
- [ ] Follow-up — move spec to `completed/` once merged.
