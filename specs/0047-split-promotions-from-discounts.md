# 0047 — Promotions: a campaign layer over discounts

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-06-30
- TODO item: Split out Promotions concept from Discounts

## Problem

Discounts are **flat**. Each `Discount` is a self-contained offer — its own coupon, schedule, conditions (`min_prices`, customers, customer groups, channels), priority/`stop`, a type driver (`AmountOff` / `BuyXGetY`) and `data`, and its scope pivots (`discountables`, `collection_discount`, `brand_discount`). There is no layer **above** a discount to group several related offers into one marketing campaign.

A real campaign is exactly that grouping. "World Cup 2026" is one campaign carrying many distinct offers — 20% off England shirts, buy-one-get-one on footballs, free shipping over GBP 50 — each with its **own** conditional logic, but all belonging to, scheduled with, and reported against the one campaign. Today a merchant can only model these as unrelated discounts with no shared identity, no shared window, and nothing for a storefront to address as "the World Cup campaign."

The domain already gestures at the concept and leaves it unbuilt: a `ValueObjects\Cart\Promotion` value object and a `Cart->promotions` collection both exist but are never populated.

The gap also blocks downstream work. A campaign is the natural home for marketing presentation — banner/image management, landing pages, campaign-scoped content — which are **add-on** concerns, but they need a first-class, externally-addressable `Promotion` entity to attach to. Establishing that entity and the promotion-to-discount relationship now, **pre-alpha**, means APIs ([[0046-public-id-external-addressing]]) and add-ons build on a stable shape rather than forcing a reshape later.

Note on framing: the TODO anticipated a "breaking architectural split (table reshape)." The realised design is **additive** — discounts keep all their behaviour; a new campaign entity sits above them via a nullable foreign key. It stays pre-alpha not for migration cost but because the `Promotion` shape and relationship are foundational once external surfaces depend on them.

## Proposal

Introduce a first-class **`Promotion`** — a campaign that groups discounts — and relate each discount to at most one promotion. The discount is otherwise **unchanged**: it keeps all its own conditions, type/`data`, scope pivots, coupon, schedule, priority and `stop`. Promotions group and bound; **discounts still do the work**.

### `Promotion` model and table

New `promotions` table:

| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `public_id` | ulid, unique | externally-addressable campaign handle ([[0046-public-id-external-addressing]]) |
| `name` | translatable | campaign name ("World Cup 2026"); dedicated translatable column, mirroring [[0018-dedicated-name-description-fields]] |
| `description` | translatable, nullable | campaign blurb for storefront display |
| `handle` | string, unique | slug for campaign URLs / landing pages |
| `starts_at` | datetime, nullable, indexed | campaign window start |
| `ends_at` | datetime, nullable, indexed | campaign window end |
| `created_at` / `updated_at` | timestamps | |

```php
/**
 * @property string $handle
 * @property ?\Illuminate\Support\Carbon $starts_at
 * @property ?\Illuminate\Support\Carbon $ends_at
 */
class Promotion extends Base implements Contracts\Promotion
{
    use HasTranslations, HasPublicId, LogsActivity;

    public function discounts(): HasMany;          // Discount
    public function scopeActive(Builder $query): Builder;   // window open (or unbounded)
}
```

- `name` / `description` are translatable via the dedicated-fields pattern ([[0018-dedicated-name-description-fields]]) and read through `translate()`.
- `Promotion::active()` is the campaign-window scope: `starts_at` is null-or-past **and** `ends_at` is null-or-future. A promotion with no window is always-active.
- Core ships **no** banner/image/landing-page surface — that is add-on territory, attaching to this entity via the native extension seams ([[0041-retire-model-class-substitution]]).

### `Discount` change — one nullable column

`discounts` gains a single nullable column; nothing else on the discount moves:

```php
$table->foreignId('promotion_id')->nullable()->index();   // -> promotions
```

`Discount` gains `promotion(): BelongsTo` (nullable). A discount with no promotion is a standalone offer, exactly as today. All existing discount columns, relations, scopes, conditions, type/`data` and scope pivots (`discountables`, `collection_discount`, `brand_discount`, `cart_line_discount`) are **untouched**.

### The campaign window bounds its discounts

A discount applies when its **own** conditions pass **and** its campaign window (if any) is open. The campaign window is the one place a merchant pauses or schedules an entire campaign; individual discounts still govern themselves through their own schedule, coupon, min-spend, audience and usage caps.

`DiscountManager::getDiscounts` adds the promotion-window gate to its candidate query, alongside the existing `active()` / `usable()` / channel / customer-group / coupon filters:

```php
Discount::active()
    ->usable()
    ->channel($this->channels)
    ->customerGroup($this->customerGroups)
    ->where(fn ($q) => $q
        ->whereNull('promotion_id')
        ->orWhereHas('promotion', fn ($p) => $p->active())
    )
    // ...existing cart-scope and coupon filters unchanged
```

`DiscountManager::apply` and every discount type's `apply()` are otherwise unchanged — promotions never apply; they gate and group. The `Discounts` facade keeps its name (it applies discounts, not promotions).

### Surfacing the campaign on cart and order

When discounts apply, the campaigns behind them are surfaced — finally populating the stubbed `Cart->promotions`:

- **Cart** — `Cart->promotions` is filled with one `ValueObjects\Cart\Promotion` per distinct campaign behind the applied discounts (its `reference` = the promotion `handle` / `public_id`, `description` = the campaign name, `amount` = the summed discount the campaign contributed). The existing value object and collection stub are repurposed for this.
- **Order** — each entry in the `order.discount_breakdown` JSON snapshot records the originating `promotion_id` + `handle` (null for standalone discounts), so a placed order answers "which campaign discounted this" without a live join. `ResolvePaymentStatus` and all money rollups are untouched — this is additive bookkeeping.

This gives campaign-level reporting and storefront display ("this order benefited from World Cup 2026") out of the box.

### Filament

- A new **`PromotionResource`** — CRUD for campaigns (translatable `name`/`description`, `handle`, the `starts_at`/`ends_at` window), with a **relation manager listing its discounts** (add existing / create new discounts under the campaign).
- The existing `DiscountResource` form gains a **promotion select** (associate this discount with a campaign, or leave standalone). Everything else on the discount form is unchanged.

## Alternatives considered

- **Do nothing — discounts stay flat.** Rejected: there is no way to group related offers into a campaign, no shared campaign identity/window, and nothing externally addressable for storefront campaigns or the planned banner/landing-page add-ons to attach to.
- **Move conditions/eligibility onto the promotion (the original draft of this spec).** Rejected at the author's direction: discounts under one campaign carry **differing** conditional logic ("20% off shirts" vs "BOGOF footballs" vs "free shipping over GBP 50"), so conditions belong on each discount, not hoisted to the shared campaign. The promotion groups and bounds; it does not own the offers' logic.
- **Make `Promotion` the unit the cart applies, with `Discount` as a thin effect row under it.** Rejected — it would reshape the whole discount-application pipeline and the scope pivots for no gain, when the existing per-discount `apply()` already does exactly the right thing. Promotions add a layer; they do not replace the discount engine.
- **Model a campaign as a tag/label on discounts.** Rejected — a campaign needs to be a first-class, externally-addressable entity (its own translatable name/description, window, `public_id`) that add-ons extend with media and content. A string tag carries none of that.
- **Promotion window as pure display metadata (no gating).** Considered; rejected in favour of the window bounding its discounts, so a merchant can pause/schedule an entire campaign in one place. The cost is one `orWhereHas('promotion', active)` clause in the candidate query.
- **A polymorphic `promotion_conditions` table for campaign-level "must contain product X" eligibility.** Rejected as speculative — no such campaign-level condition exists in the model today (the only cart-content gate is per-discount min-spend), and conditions stay on the discount by design. Addable additively if a real need appears.

## Migration impact

- **Database** (baseline editable, v2 pre-release): new `promotions` table; one nullable `discounts.promotion_id` column. The discount scope pivots and all other discount columns are **unchanged**. This is additive.
- **Breaking changes to the public contract surface:** minimal and additive — new `Lunar\Core\Models\Promotion` + `Contracts\Promotion`; `Discount::promotion()` relation; `Promotion::active()` scope; the promotion-window clause in `DiscountManager::getDiscounts`; promotion fields in `Cart->promotions` and the `order.discount_breakdown` snapshot. No existing discount surface is removed or renamed, so **no Rector rule** is required.
- **Upgrade path for v1.x consumers:** additive — the upgrade package creates the `promotions` table and adds the nullable `promotion_id`. v1 has no promotion concept, so existing discounts simply carry `promotion_id = null` (standalone) and keep working unchanged; merchants group them into campaigns afterwards in the admin. No data backfill, no `down()` needed (per the one-way migration policy).
- **Translation / locale impact:** new `Promotion` translatable `name`/`description` (no v1 data to migrate) and `PromotionResource` admin labels, English-first then mirrored across the other 15 locales.
- **Filament / admin impact:** new `PromotionResource` (CRUD + discounts relation manager); a promotion select added to the discount form. The discount admin is otherwise unchanged.

## Resolved decisions

- **Promotion is a campaign grouping, not a participant in cart logic.** It groups and bounds discounts; discounts keep all conditions, type/`data`, scope and coupon. The `Discounts` facade and `DiscountManager::apply` keep applying *discounts*.
- **Discount change is one nullable `promotion_id`.** Standalone discounts (null promotion) still work; nothing moves off the discount.
- **Promotion carries a campaign window + translatable identity.** Translatable `name`/`description` (per the 0018 dedicated-fields pattern), `handle`, nullable `starts_at`/`ends_at`, `public_id`. Banners/landing pages are add-on concerns attaching to this entity.
- **The campaign window gates application.** A discount applies only when its own conditions pass and (it has no promotion, or its promotion's window is active) — one `orWhereHas('promotion', active)` clause in the candidate query. Lets a merchant pause/schedule a whole campaign at once.
- **Applied campaigns surface on cart and order.** `Cart->promotions` (repurposing the existing stub) and a `promotion_id`/`handle` entry in the `order.discount_breakdown` snapshot enable campaign-level reporting/display.
- **Realised as an additive change**, not the table reshape the TODO anticipated — still landed pre-alpha because the `Promotion` shape and relationship are foundational for external addressing and add-ons.

## References

- `Lunar\Core\Models\Discount` — gains a nullable `promotion_id` and `promotion()`; otherwise unchanged.
- `Lunar\Core\Managers\DiscountManager` / `Facades\Discounts` — the candidate query gains the promotion-window gate; application is otherwise unchanged.
- `Lunar\Core\ValueObjects\Cart\Promotion` / `Cart->promotions` — the stub this finally populates.
- `Lunar\Core\Casts\DiscountBreakdown` / `order.discount_breakdown` — the snapshot that records the originating campaign.
- [[0018-dedicated-name-description-fields]] — the translatable dedicated-column pattern `Promotion.name`/`description` follow.
- [[0046-public-id-external-addressing]] — `Promotion` is the externally-addressable campaign (`public_id`); if 0046 lands first, the promotion adopts `HasPublicId` from it.
- [[0041-retire-model-class-substitution]] — the native extension seams add-ons use to attach banner/content management to `Promotion`.
- Upgrade data migrations are one-way (not reversible; restore from backup to undo).

## Implementation plan

- [ ] Slice 1 — model + relationship. `promotions` table (translatable `name`/`description`, `handle`, window, `public_id`); `Promotion` model + `Contracts\Promotion` (`discounts()` hasMany, `active()` window scope, `HasTranslations`/`HasPublicId`/`LogsActivity`); nullable `discounts.promotion_id` + `Discount::promotion()`.
- [ ] Slice 2 — application gating + surfacing. Promotion-window clause in `DiscountManager::getDiscounts`; populate `Cart->promotions` from applied discounts (repurposing the stub VO); record `promotion_id`/`handle` in the `order.discount_breakdown` snapshot.
- [ ] Slice 3 — Filament. `PromotionResource` (CRUD + discounts relation manager); promotion select on the discount form; translations across 16 locales.
- [ ] Slice 4 — upgrade package. Additive migration creating `promotions` and adding nullable `promotion_id`; no backfill, no Rector, no `down()`.
