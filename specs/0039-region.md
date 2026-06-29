# 0039 — Region

- Status: accepted
- Author: Glenn Jacobs
- Created: 2026-06-29
- TODO item: Region concept to define channel, currency, language, tax_zone, countries and price display

## Problem

A storefront request today resolves its market context from a handful of independently-modelled, independently-resolved primitives:

- `Channel` — the sales surface (`Channel::getDefault()`), stored on `Cart`/`Order` as `channel_id`.
- `Currency` — money (`Currency::getDefault()`), stored on `Cart`/`Order` as `currency_id`.
- `Language` — translation locale (`Language::getDefault()`), read via `translate()`.
- `TaxZone` — tax behaviour, resolved per shipping address (postcode -> state -> country -> default) in `GetTaxZone`, with an unused `price_display` column.
- `Country` — global reference data, not scoped to anything.
- Price display (tax inclusive/exclusive) — a single global `config('lunar.pricing.stored_inclusive_of_tax')` with no per-market override; `TaxZone::price_display` exists but is never read.

`StorefrontSessionManager` resolves channel, currency and customer groups separately (`initChannel()`, `initCurrency()`, …), each falling back to its own `getDefault()`. Nothing binds these choices together. A merchant who wants to trade in more than one market — "UK: GBP, English, UK VAT, prices shown inc-tax, ships to GB"; "US: USD, English, US sales tax, prices shown ex-tax, ships to US" — has no object that expresses that grouping. They must wire the combination by hand on every request, and there is no record on a cart or order of *which market* it belonged to.

This is the cheapest foundational item to land first. Region reshapes currency / channel / tax / price-display resolution and adds FKs to `Cart` and `Order`; retrofitting it once adopters have live carts, orders and integration code built against the loose primitives is painful. Landing it before the alpha gets the market-context shape right while the surface is still unstable.

## Proposal

Introduce a first-class `Region` that groups the market-presentation primitives behind one record, seed a default region so single-market stores never think about it, and make storefront resolution and price display region-aware.

### What a Region is

A `Region` is a **market**: a named grouping that answers "for buyers in this market, what money, language, tax behaviour and price display do we present, and which countries does it serve". It is the market axis. `Channel` stays the **sales-surface** axis (online store, B2B portal, POS) that scopes the catalogue via the existing `channelables` table. The two are orthogonal: an "Online Store" channel can operate a UK, a US and an EU region.

For the alpha a `Region` **belongs to one `Channel`** (a channel has many regions, one flagged default). This matches the TODO framing ("Region … define channel") and keeps the model small: resolving a region resolves its channel. The richer many-to-many (one region config reused across channels, Shopify Markets style) is deferred — see Alternatives.

### `regions` table

| column          | type                    | notes                                                        |
| --------------- | ----------------------- | ------------------------------------------------------------ |
| `id`            | id                      |                                                              |
| `name`          | string                  | display name ("United Kingdom", "European Union")            |
| `handle`        | string, unique          | stable slug for addressing (`uk`, `eu`)                      |
| `channel_id`    | FK -> channels          | the sales surface this region operates on                    |
| `currency_id`   | FK -> currencies        | default currency presented in this region                    |
| `language_id`   | FK -> languages         | default language presented in this region                    |
| `tax_zone_id`   | FK -> tax_zones, null   | display/default tax zone for catalogue pricing (see Tax)     |
| `prices_inc_tax`| boolean, null           | display prices inclusive of tax; null -> global config default |
| `default`       | boolean, indexed        | exactly one default region (`HasDefaultRecord`)              |
| `timestamps`    |                         |                                                              |

`country_region` pivot (`country_id`, `region_id`) — the countries each region serves. Used to map a visitor's country to a region and to scope address country options. A country may map to at most one region in the alpha (enforced in the resolver, not the schema).

The model lives at `Lunar\Core\Models\Region`, extends `Models\Base`, binds a `Lunar\Core\Models\Contracts\Region` contract, uses `HasDefaultRecord`, and exposes `channel()`, `currency()`, `language()`, `taxZone()`, `countries()` relations plus a `RegionFactory`.

### Resolution

Region is the spine that the other selections default from. The default cascade lives in `ResolveStorefrontContext` ([[0040-storefront-context]], which lands first); Region extends *that* resolver rather than re-implementing resolution per call site:

1. **Region** — explicit override -> session (`lunar_storefront_region` by handle) -> visitor country mapped via `country_region` (hook, off by default) -> `Region::getDefault()`.
2. **Channel** — `region->channel` (still individually settable; an explicit channel must belong to the region).
3. **Currency** — explicit override -> `region->currency`.
4. **Language** — explicit override -> `region->language`.

Currency and language stay individually overridable (preserving today's behaviour) but now *default from the region* instead of from their own global `getDefault()`. The resolved values are carried on the `StorefrontContext` DTO, whose nullable `region` slot this spec populates. `StorefrontSession` gains `getRegion()` / `setRegion()` (and feeds region into the context it produces).

A `StorefrontContext` is the *resolved snapshot* of these selections for one operation; a `Region` is the stored market record that *provides their defaults*. The context composes a region — it does not implement a region, and a region does not implement the context. See [[0040-storefront-context]].

### Price display

- **Storage** stays global: `config('lunar.pricing.stored_inclusive_of_tax')` is unchanged — a single price row cannot be stored inc-tax for one region and ex-tax for another.
- **Display** becomes region-aware. The `prices_inc_tax()` helper resolves the current region's `prices_inc_tax` flag, falling back to the global config when the region leaves it null. `Price::priceIncTax()` / `priceExTax()` already accept a tax zone; catalogue display passes the region's `tax_zone_id` when no explicit zone is given.
- `TaxZone::price_display` (currently dead) is left as-is; the region flag is the live display control. A follow-up may retire the column.

### Tax

Region carries a **display** tax zone (`tax_zone_id`) used to price the catalogue before a shipping address is known. Checkout resolution is unchanged: once a cart has a shipping address, `GetTaxZone` still resolves postcode -> state -> country -> default and that wins. Region does not replace address-based tax resolution; it seeds the pre-address display zone.

### Cart and Order

`carts` and `orders` gain a nullable `region_id` FK. `channel_id` and `currency_id` stay (denormalised; the catalogue-scoping queries already key off `channel_id`), with the invariant that a cart/order's `channel_id` equals `region->channel_id` and its `currency_id` defaults from the region. `CartSessionManager::createNewCart()` stamps `region_id` from the resolved region; `setChannel()`/`setCurrency()` continue to work and remain consistent with the region.

### Default seed

A default `Region` is seeded from the existing default channel, currency, language and tax zone, flagged `default`, serving the default country set (or all countries until configured). Single-market stores get a working region with zero configuration; everything resolves through it transparently, matching the "features work out of the box" principle.

### Filament

A `RegionResource` (list / create / edit) under the bridge package: name, handle, channel, currency, language, display tax zone, prices-inc-tax toggle, served-countries multiselect, default toggle. Currency/language pickers elsewhere stay, now seeded from the active region.

## Alternatives considered

- **Many-to-many Channel <-> Region (Shopify Markets).** A region config (currency/language/tax/countries) reused across several channels via a `channel_region` pivot. More expressive, and the long-term target. Deferred: it is additive (a pivot can be introduced later without reshaping existing rows), whereas the painful-to-retrofit parts — the `Cart`/`Order` FK and the resolution semantics — are landed now. For the alpha a region config is cheap to duplicate per channel.
- **Region replaces Channel.** Collapse the two axes into one. Rejected: channel already does real work (catalogue scoping via `channelables`, scheduled per-channel availability) on an axis orthogonal to market; merging them loses "one sales surface, many markets".
- **Multiple currencies / languages per region (primary + alternates).** Shopify allows a market to accept several currencies. Deferred to a follow-on: the supported-currency/language sets are additive pivots; the alpha ships one default currency and one default language per region, which covers the representative case without a retrofit cost.
- **Do nothing (single-region alpha).** Viable short-term, but every adopter who builds multi-market by hand bakes assumptions against the loose primitives, and the `Cart`/`Order` FK plus resolution reshape become a breaking migration against live data. Cheaper to land the shape first.

## Migration impact

- **Database migrations:** new `regions` table; new `country_region` pivot; nullable `region_id` added to `carts` and `orders`. Additive — no baseline edits (v2 ships a flat baseline; schema changes go in new migrations).
- **Breaking changes to the public contract surface:** `StorefrontSession` contract gains `getRegion()`/`setRegion()` (additive). Currency/language default *source* changes from each primitive's `getDefault()` to the resolved region's default — behaviourally equivalent for a single-region store, but a contract shift worth a Rector note. New `Region` model + contract are additive public surface.
- **Upgrade path for v1.x consumers:** the upgrade package seeds a single default `Region` from the v1 default channel/currency/language/tax-zone and backfills `region_id` on existing carts and orders to it. One-way (no `down()`), consistent with the upgrade-migration convention.
- **Translation / locale impact:** new Filament `RegionResource` label/field keys added to all 16 locales (English first, mirrored across the rest).
- **Filament / admin impact:** new `RegionResource`; storefront currency/language resolution seeded from the active region.

## Open questions

- **Channel <-> Region cardinality.** Resolved: **belongs-to-channel for the alpha** (`regions.channel_id`); a region config is duplicated per channel if needed. The `channel_region` pivot can be introduced later additively, without reshaping existing rows.
- **Country -> region resolution.** Ship the visitor-country -> region hook disabled by default (explicit/session/default only), or wire a default geo source? Leaning disabled — geo-IP is host-app territory.
- **`currency_id` on Cart/Order.** Keep it denormalised alongside `region_id`, or derive currency from the region? Leaning keep (back-compat, and per-cart currency override stays possible).
- **`TaxZone::price_display`.** Retire the dead column in this spec, or leave for a separate cleanup? Leaning leave.
- **Customer-group interaction.** `TaxZone` already filters by customer group; does region selection compose with, or sit beneath, customer-group resolution? Likely orthogonal — confirm.

## References

- TODO: "Region concept …" (pre-alpha). [[0040-storefront-context]] lands first and is the seam Region's defaults feed into.
- Prior art: Shopify Markets (countries -> currency/language/price/tax behaviour).
- Related specs: [[draft-storefront-api]] (context headers `X-Lunar-Channel` / `X-Lunar-Currency` / `Accept-Language` will extend to region), [[0038-inventory-fundamentals]] (location/stock routing references "a channel / region / collection point maps to the location(s) that may serve it").
- Current surface: `Models/Channel.php`, `Models/Currency.php`, `Models/Language.php`, `Models/TaxZone.php`, `Models/Country.php`, `Managers/StorefrontSessionManager.php`, `Managers/CartSessionManager.php`, `Contracts/StorefrontSession.php`, `Actions/Taxes/GetTaxZone.php`, `config/pricing.php`, `helpers.php` (`prices_inc_tax()`).

## Implementation plan

- [x] Slice 1 — `Region` model, contract, `regions` + `country_region` migrations, `HasDefaultRecord`, factory; default-region seed wired into install.
- [x] Slice 2 — `region_id` on `carts` and `orders`; `CartSessionManager` stamps the default region on new carts and `FillOrderFromCart` copies it to the order; `Cart`/`Order` gain the `region()` relation. (Region-aware resolution of the stamped region, beyond the default, lands in slice 3.)
- [ ] Slice 3 — region resolution: extend `ResolveStorefrontContext` to cascade through the region, populate the context's `region` slot, add `StorefrontSession` `getRegion`/`setRegion`.
- [ ] Slice 4 — region-aware price display (`prices_inc_tax()` + catalogue tax zone from region).
- [ ] Slice 5 — Filament `RegionResource` + 16-locale translations.
- [ ] Slice 6 — upgrade-package default-region seed + `region_id` backfill for v1.x consumers.
