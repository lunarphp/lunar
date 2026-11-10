# Discount coupons

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-11-10
- TODO item: Discount coupons — a discount has many coupon codes, each with its own usage limit and optional customer, and codes can be generated in bulk

## Problem

A discount holds at most one coupon code, in the `discounts.coupon` column. Merchants running email or loyalty campaigns want a code per recipient: one discount, thousands of single-use codes, each redeemable once and optionally only by the customer it was issued to. Lunar has no way to model that.

An add-on cannot add it either. The one-code-per-discount assumption is hardcoded in three places, and only one of them is a swappable seam:

| Where | What it does | Swappable |
| --- | --- | --- |
| `Validation\CouponValidator` | Finds an active, usable discount where `coupon = :code` | Yes, through `Contracts\CouponValidator` |
| `Managers\DiscountManager::getDiscounts()` | Narrows the discount query with `where('coupon', $code)->orWhereNull('coupon')` | No |
| `DiscountTypes\AbstractDiscountType::checkDiscountConditions()` | Compares `$cart->coupon_code` to `$discount->coupon` as strings | No |

Usage limits only exist per discount (`uses`, `max_uses`, `max_uses_per_user`), so there is nowhere to record that one code is spent while the others are not.

Usage counting is also racy. `AbstractDiscountType::markAsUsed()` increments `uses` in PHP and `CreateOrder` saves the model, so two concurrent checkouts can both pass `uses < max_uses` and both consume the last use. With one shared code that over-redeems a capped promotion. With single-use codes it lets a code be redeemed twice.

## Proposal

Coupons become rows. A discount is either applied automatically or redeemed with a coupon, and a coupon discount has one or more codes.

### Schema

New `{prefix}coupons` table, added to the baseline migrations while v2 is in alpha:

| Column | Type | Notes |
| --- | --- | --- |
| `id` | `id` | |
| `discount_id` | `foreignId` | Constrained to `discounts`, cascade on delete |
| `code` | `string` `unique` | Stored uppercase through `Casts\CouponString` |
| `uses` | `unsignedInteger` default `0` | Redemptions of this code |
| `max_uses` | `unsignedInteger` `nullable` | Null is unlimited |
| `customer_id` | `foreignId` `nullable` | Constrained to `customers`, null on delete. When set, only this customer can redeem the code |
| `batch` | `ulid` `nullable` `index` | Groups the codes made by one generation run |
| `timestamps` | | |

Changes to `{prefix}discounts`:

- Drop `coupon`.
- Add `redemption`, a string backed by a new `Enums\DiscountRedemption` enum with cases `Automatic` and `Coupon`, indexed, default `automatic`.

`redemption` is explicit rather than derived from "has any coupons". Deriving it means deleting the last code of a coupon discount silently turns it into an automatic discount that applies to every cart.

`discounts.uses` and `discounts.max_uses` stay, and keep meaning the total across the discount: a campaign of 5,000 single-use codes can still be capped at 1,000 redemptions. `max_uses_per_user` stays on the discount.

### Models

`Models\Coupon` extends `Models\Base`, with a factory.

- `discount(): BelongsTo`
- `customer(): BelongsTo`
- `scopeUsable(Builder $query)`: `uses < max_uses` or `max_uses` is null.
- `isRedeemableBy(?Customer $customer): bool`: usable, and `customer_id` is null or matches.

`Models\Discount` gains:

- `coupons(): HasMany`
- `redemption` cast to `DiscountRedemption`
- `generateCoupons(int $quantity, CouponFormat $format): string`, a verb that delegates to the generate action and returns the batch ULID.

`Models\Cart` gains `coupon(): BelongsTo`, keyed `coupon_code` → `coupons.code`. Both columns are uppercased by the same cast, so a plain relation resolves the code with one indexed lookup, and it can be eager loaded.

### Resolving a code

Every place that matches a cart's code goes through `$cart->coupon`:

- `DiscountManager::getDiscounts()` selects discounts where `redemption = automatic`, or `id = $cart->coupon->discount_id` when the cart has a coupon that resolves.
- `AbstractDiscountType::checkDiscountConditions()` passes a coupon discount only when `$cart->coupon?->discount_id` is this discount and `$cart->coupon->isRedeemableBy($cart->customer)`, unless the cart has already consumed this discount (the existing `consumedDiscountIds()` exemption).
- `Validation\CouponValidator` finds the coupon by code, then checks it is redeemable by the current customer and that its discount is active, usable and within `max_uses_per_user`. `Contracts\CouponValidator` and `Rules\ValidCoupon` keep their signatures.

`DiscountManager::getDiscountsCacheKey()` already includes `coupon_code`, so the memoised discount set stays correct.

### Consuming a code

`CreateOrder` consumes the coupon and the discount with conditional atomic updates inside its transaction, replacing the read-increment-save in `markAsUsed()`:

```sql
UPDATE coupons   SET uses = uses + 1 WHERE id = ? AND (max_uses IS NULL OR uses < max_uses)
UPDATE discounts SET uses = uses + 1 WHERE id = ? AND (max_uses IS NULL OR uses < max_uses)
```

If either update affects no rows, `CreateOrder` throws a new `Exceptions\CouponExhaustedException` and the transaction rolls back. The storefront catches it, removes the code and re-prices the cart. Retries for the same cart skip consumption through the existing `consumedDiscountIds()` check, so a declined card and a retry still consume one use.

`markAsUsed()` keeps attaching the user to `discount_user` for `max_uses_per_user`, and stops touching `uses`.

### Recording the code on the order

`Pipelines\Order\Creation\MapDiscountBreakdown` adds `coupon_id` and `coupon_code` to each breakdown entry that came from a coupon discount. The code is copied, so the order still shows it after the coupon is deleted.

### Generating codes

- `Contracts\CouponCodeGenerator` with `generate(CouponFormat $format): string`, bound in `LunarServiceProvider::registerServices()`. The default, `Generators\CouponCodeGenerator`, draws from an alphabet without look-alike characters (no `0`, `O`, `1`, `I`, `L`).
- `DataObjects\CouponFormat`: `length` (default 10), `prefix` (default none), `maxUses` (default 1), `customerId` (default none).
- `Contracts\Discounts\GeneratesCoupons` → `Actions\Discounts\GenerateCoupons`, registered in `ActionServiceProvider`. `execute(Discount $discount, int $quantity, CouponFormat $format): string` creates a batch ULID, generates codes in chunks of 1,000, inserts with `insertOrIgnore`, and keeps going until `$quantity` rows exist for the batch, so a code that collides with an existing one is replaced, not lost. It returns the batch ULID.
- Above a threshold (500 codes), the panel dispatches `Jobs\GenerateCouponsJob`, which calls the action. Code callers choose for themselves.

Issuing one code to one customer is a single call with `quantity: 1` and `customerId` set.

### Panel

On the discount edit page:

- A redemption choice, "Applied automatically" or "Needs a coupon", replaces "leave the coupon blank to apply automatically".
- A Coupons section, shown for coupon discounts:
  - A table of codes with uses, limit, customer and batch, searchable by code.
  - Add code: one code typed by hand. This covers the single shared code used today.
  - Generate codes: quantity, prefix, length, uses per code.
  - Export to CSV, for all codes or one batch.
  - Delete code.

The index's redemption filter reads `redemption`, and the coupon column shows the code when there is one and the count when there are several.

### Out of scope

Pushing codes to email platforms, merge tags, and campaign attribution. Integrations build on `generateCoupons()` and the export.

## Alternatives considered

- **Keep one code per discount and duplicate the discount per code.** It works today with no change, but 5,000 discounts per campaign floods the discount index, every cart calculation loads them, and editing the campaign means editing every copy.
- **Leave it to an add-on, and make core extensible with a `CouponResolver` contract.** It is the smallest core change, but the add-on would still have to replace usage counting and the order record. Codes as rows is the general model, and single shared codes are the one-row case of it.
- **A `CouponResolver` contract alongside the model.** Once codes are rows, `Cart::coupon()` is the resolution. `CouponValidator` stays swappable for validation rules. A resolver contract can be added later if a real external source of codes appears.
- **A `coupon_batches` table with names and metadata.** The `batch` ULID covers grouping and export. A batches table is an additive change if campaigns need names or notes later.

## Migration impact

- **Database:** new `coupons` table; `discounts.coupon` dropped and `discounts.redemption` added. Both folded into the baseline migrations while v2 is in alpha.
- **Breaking changes:**
  - `Discount::$coupon` is gone. Code that reads it uses `$discount->coupons`, and code that writes it creates a `Coupon`.
  - `AbstractDiscountType::markAsUsed()` no longer increments `uses`. Custom discount types that override it are unaffected unless they relied on that.
  - `CreateOrder` can throw `CouponExhaustedException`.
- **Upgrade from v1.x:** the upgrade package's data migration step creates a `Coupon` for every v1 discount with a coupon, copying `uses` and `max_uses`, and sets `redemption` to `coupon` for those discounts and `automatic` for the rest. A Rector rule is not practical for `$discount->coupon` reads, because the replacement changes shape. The upgrade notes call it out instead.
- **Translations:** new keys in `packages/panel/resources/lang/*/discounts.php` for the redemption choice, the Coupons section, generation and export, in all 16 locales. `field_coupon_hint` is removed.
- **Panel:** the discount edit page and index as described above.
- **Docs:** `docs/reference/discounts.mdx` (the `Coupon` model, `redemption`, generation), `docs/guides/cart.mdx` (handling `CouponExhaustedException`), and `docs/extending/discounts.mdx` (custom types and `$cart->coupon`).

## Open questions

- Should `CouponExhaustedException` from `CreateOrder` be the behavior, or should `CreateOrder` drop the coupon and place the order at the re-priced total? Throwing is safer because the shopper sees the price change. Owner: Glenn, before `accepted`.
- Is 500 the right threshold for queueing generation in the panel? Settle it with a timing test on Postgres and MySQL during slice 3.
- Should a customer-bound code also be redeemable by a guest cart that later attaches to that customer at checkout? The proposal says no: the customer must be on the cart when conditions are checked.

## References

- Community request for per-customer unique discount codes.
- [[0072-panel-discounts-section]], the panel discount screens this extends.
- [[0073-split-amount-off-discount-type]], the previous discount schema change and its upgrade step.

## Implementation plan

- [ ] Slice 1 — Core model: `coupons` table, `redemption` column, `Coupon` model and factory, `Cart::coupon()`, matching in `DiscountManager`, `AbstractDiscountType` and `CouponValidator`, atomic consumption in `CreateOrder`, and the coupon on the order breakdown. Existing behavior covered by moving each single-code test to a one-row coupon.
- [ ] Slice 2 — Upgrade: v1 `discounts.coupon` → `coupons` in the data migration step, and upgrade notes.
- [ ] Slice 3 — Generation: `CouponCodeGenerator`, `CouponFormat`, `GenerateCoupons` action and job, and `Discount::generateCoupons()`.
- [ ] Slice 4 — Panel: redemption choice, Coupons section, generation, CSV export, index filter and column, translations in all 16 locales.
- [ ] Slice 5 — Docs.
