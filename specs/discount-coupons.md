# Discount coupons

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-11-10
- TODO item: Discount coupons — a discount has many coupon codes, each with its own usage limit and optional recipient, and codes can be generated in bulk

## Problem

A discount holds at most one coupon code, in the `discounts.coupon` column. Merchants running email or loyalty campaigns want a code per recipient: one discount, thousands of single-use codes, each redeemable once. Lunar has no way to model that.

### Limiting use per person needs a signed-in user

The only per-person limit is `discounts.max_uses_per_user`, and it counts rows in `discount_user`, which records the signed-in user. A guest cart has no user, so `AbstractDiscountType::checkDiscountConditions()` fails any discount that sets `max_uses_per_user` for every guest cart. `CouponValidator` checks the limit only when `auth()->user()` is set.

Many stores don't require shoppers to sign in. A merchant emailing existing customers can't guarantee each of them uses the offer once: either guests are locked out, or the limit doesn't apply. A unique single-use code solves this without knowing who the shopper is, because the code is the credential. Whoever holds it can redeem it once.

A community member built exactly this on 1.3 as a custom discount type and a `discount_coupon_codes` table:

```php
Schema::create('discount_coupon_codes', function (Blueprint $t) {
    $t->id();
    $t->foreignId('discount_id')->constrained('lunar_discounts')->cascadeOnDelete();
    $t->string('code')->unique();
    $t->string('email')->nullable();
    $t->foreignId('cart_id')->nullable();
    $t->timestamp('redeemed_at')->nullable();
    $t->timestamps();
});
```

This proposal follows that design. The code carries the guarantee, `email` records who the code was sent to, and a redemption record captures when and where it was used.

### Core has no seam for it

An add-on has to work around core rather than extend it. The one-code-per-discount assumption is hardcoded in three places, and only one of them is a swappable seam:

| Where | What it does | Swappable |
| --- | --- | --- |
| `Validation\CouponValidator` | Finds an active, usable discount where `coupon = :code` | Yes, through `Contracts\CouponValidator` |
| `Managers\DiscountManager::getDiscounts()` | Narrows the discount query with `where('coupon', $code)->orWhereNull('coupon')` | No |
| `DiscountTypes\AbstractDiscountType::checkDiscountConditions()` | Compares `$cart->coupon_code` to `$discount->coupon` as strings | No |

Usage limits only exist per discount (`uses`, `max_uses`), so there is nowhere to record that one code is spent while the others are not.

### Usage is counted when the draft order is created

`CreateOrder` calls `markAsUsed()` for every applied discount as soon as it creates the draft order, before any payment succeeds. A draft order is not a sale:

- A shopper whose card is declined and who comes back on a different cart (another device, an expired session) finds a single-use code already spent.
- Every abandoned checkout permanently uses up part of a capped promotion. `PruneCarts` doesn't help: it is off by default and removes carts after 90 days.
- To stop a retry on the same cart from being blocked by its own use, core carries a workaround: `Cart::consumedDiscountIds()`, `Cart::forgetConsumedDiscountIds()`, and the `$exempt` argument to `Discount::scopeUsable()`.

The count is also racy. `markAsUsed()` increments `uses` in PHP and `CreateOrder` saves the model, so two concurrent checkouts can both pass `uses < max_uses` and both take the last use.

Checking the code at order creation can't reject it either. PayPal, and Stripe with automatic capture, call `$cart->createOrder()` after the payment has been taken, so throwing there charges the shopper and gives them no order.

## Proposal

Coupons become rows. A discount is either applied automatically or redeemed with a coupon, and a coupon discount has one or more codes. Redeeming a code needs no signed-in user and no customer on the cart.

Usage of every discount, with or without a coupon, is recorded when the order is placed, in a redemptions table that also replaces `discount_user`.

### Schema

New tables, added to the baseline migrations while v2 is in alpha.

`{prefix}coupons`:

| Column | Type | Notes |
| --- | --- | --- |
| `id` | `id` | |
| `discount_id` | `foreignId` | Constrained to `discounts`, cascade on delete |
| `code` | `string` `unique` | Stored uppercase through `Casts\CouponString` |
| `uses` | `unsignedInteger` default `0` | Redemptions of this code on placed orders |
| `max_uses` | `unsignedInteger` `nullable` | Null is unlimited. Generated codes default to `1` |
| `email` | `string` `nullable` `index` | Who the code was issued to. Recorded for export and lookup, not checked at redemption |
| `batch` | `ulid` `nullable` `index` | Groups the codes made by one generation run |
| `timestamps` | | |

`{prefix}discount_redemptions`, one row per discount per placed order:

| Column | Type | Notes |
| --- | --- | --- |
| `id` | `id` | |
| `discount_id` | `foreignId` | Constrained to `discounts`, cascade on delete |
| `coupon_id` | `foreignId` `nullable` | Constrained to `coupons`, null on delete |
| `order_id` | `foreignId` `nullable` | Constrained to `orders`, null on delete. Null only for rows carried over from v1 |
| `cart_id` | `foreignId` `nullable` | Constrained to `carts`, null on delete |
| `user_id` | `userForeignKey` `nullable` | The signed-in user, for `max_uses_per_user` |
| `email` | `string` `nullable` | The customer's email, normalized. Written and counted by [[guest-discount-usage-limits]]; null until then, and for rows carried over from v1 |
| `redeemed_at` | `timestamp` `index` | When the order was placed |

A unique index on `(discount_id, order_id)` means an order redeems a discount at most once, however many times `OrderPlaced` fires for it. An index on `(discount_id, email)` serves per-customer counts.

`discount_user` is dropped. `discount_redemptions.user_id` records the same thing, and `Discount::users()` keeps working as a `BelongsToMany` through `discount_redemptions`.

Changes to `{prefix}discounts`:

- Drop `coupon`.
- Add `redemption`, a string backed by a new `Enums\DiscountRedemption` enum with cases `Automatic` and `Coupon`, indexed, default `automatic`.

`redemption` is explicit rather than derived from "has any coupons". Deriving it means deleting the last code of a coupon discount silently turns it into an automatic discount that applies to every cart.

`discounts.uses` and `discounts.max_uses` stay, and keep meaning the total across the discount: a campaign of 5,000 single-use codes can still be capped at 1,000 redemptions. Both `uses` columns are counters for fast eligibility checks; `discount_redemptions` is the record. `max_uses_per_user` stays, and [[guest-discount-usage-limits]] extends it to guests by email.

### Models

`Models\Coupon` extends `Models\Base`, with a factory.

- `discount(): BelongsTo`
- `redemptions(): HasMany`
- `scopeUsable(Builder $query)`: `max_uses` is null or `uses < max_uses`.
- `isUsable(): bool`: the same check on a loaded coupon.

`Models\DiscountRedemption` extends `Models\Base`, with `discount()`, `coupon()`, `order()`, `cart()` and `user()`.

`Models\Discount` gains:

- `coupons(): HasMany`
- `redemptions(): HasMany`
- `redemption` cast to `DiscountRedemption`
- `generateCoupons(CouponBatch $batch): string`, a verb that delegates to the generate action and returns the batch ULID.

`Discount::scopeUsable()` loses its `$exempt` argument.

`Models\Cart` gains `coupon(): BelongsTo`, keyed `coupon_code` → `coupons.code`. Both columns are uppercased by the same cast, so a plain relation resolves the code with one indexed lookup, and it can be eager loaded. `Cart::consumedDiscountIds()` and `Cart::forgetConsumedDiscountIds()` are removed.

### Resolving a code

Every place that matches a cart's code goes through `$cart->coupon`, and none of them reads the user or the customer:

- `DiscountManager::getDiscounts()` selects usable discounts where `redemption = automatic`, or `id = $cart->coupon->discount_id` when the cart's code resolves.
- `AbstractDiscountType::checkDiscountConditions()` passes a coupon discount only when `$cart->coupon?->discount_id` is this discount and `$cart->coupon->isUsable()`.
- `Validation\CouponValidator` finds the coupon by code and checks it is usable and that its discount is active and usable. The `max_uses_per_user` check stays as it is; [[guest-discount-usage-limits]] changes how it identifies the customer. `Contracts\CouponValidator` and `Rules\ValidCoupon` keep their signatures.

A draft order no longer uses anything, so a retry on the same cart, or a new cart after a declined card, re-prices with the code with no exemption needed.

`DiscountManager::getDiscountsCacheKey()` already includes `coupon_code`, so the memoised discount set stays correct.

### Recording a redemption

`CreateOrder` no longer touches discount usage. `Pipelines\Order\Creation\MapDiscountBreakdown` adds `coupon_id` and `coupon_code` to each breakdown entry that came from a coupon discount. The code is copied, so the order still shows it after the coupon is deleted.

A new synchronous listener, `Listeners\RecordDiscountRedemptions`, handles `OrderPlaced`, wired in `LunarServiceProvider` alongside `SyncStockForOrder`. `OrderPlaced` fires from `OrderObserver` whenever `placed_at` is set, so this works the same for every payment driver. In one transaction, for each discount in the order's breakdown:

1. Insert a `discount_redemptions` row with `insertOrIgnore`. If it was ignored, this order already recorded the discount, so move on.
2. Increment `discounts.uses`, and `coupons.uses` for a coupon entry, with an atomic `UPDATE … SET uses = uses + 1`.
3. If either counter is now above its `max_uses`, dispatch `Events\Discounts\DiscountLimitExceeded` with the order, the discount and the coupon, and log an activity on the order with the event `discount-limit-exceeded` and the discount name, coupon code, uses and limit as properties.

The listener never rejects a redemption. The payment has been taken by the time an order is placed, so the order stands and the merchant is told. The activity puts it in the order timeline, so a merchant without a developer sees it. The order is not put on hold: the cost is usually small, and holding a real customer's order over it does more harm. A merchant who wants a hold adds one from a listener on `DiscountLimitExceeded`. Eligibility is enforced before payment: conditions are checked on every cart calculation, and the payment drivers check the amount being charged against a fresh total. What remains is a window of a few seconds in which two carts pay with the same last use. For a code issued to one person, that is the same person paying twice at the same moment, and the event makes it visible.

`AbstractDiscountType::markAsUsed()` is removed. Usage is recorded from the placed order, not from the discount type.

### Generating codes

- `Contracts\CouponCodeGenerator` with `generate(CouponBatch $batch): string`, bound in `LunarServiceProvider::registerServices()`. The default, `Generators\CouponCodeGenerator`, draws from an alphabet without look-alike characters (no `0`, `O`, `1`, `I`, `L`).
- `DataObjects\CouponBatch` describes one run: `length` (default 10), `prefix` (default none), `maxUses` (default 1), and either a `quantity` or a list of `emails`. `CouponBatch::ofQuantity(int $quantity)` makes anonymous codes. `CouponBatch::forEmails(iterable $emails)` makes one code per distinct, normalized address and records it in `email`.
- `Contracts\Discounts\GeneratesCoupons` → `Actions\Discounts\GenerateCoupons`, registered in `ActionServiceProvider`. `execute(Discount $discount, CouponBatch $batch): string` creates a batch ULID, generates codes in chunks of 1,000, inserts with `insertOrIgnore`, and keeps going until the batch is complete, so a code that collides with an existing one is replaced, not lost. It returns the batch ULID.
- Above `lunar.discounts.coupons.queue_threshold` codes, the panel dispatches `Jobs\GenerateCouponsJob`, which calls the action, and shows the batch as generating until it completes. Below it, the panel generates in the request. Code callers choose for themselves. The default is set from a timing test in slice 4 (1k, 5k, 10k and 50k codes on Postgres and MySQL, into a table that already holds many codes, and with the `sync` queue driver), and is expected to be a few thousand.

The campaign in the community example is `$discount->generateCoupons(CouponBatch::forEmails($customerEmails))`, then exporting the batch as email and code pairs for the mail merge.

### Panel

On the discount edit page:

- A redemption choice, "Applied automatically" or "Needs a coupon", replaces "leave the coupon blank to apply automatically".
- A Coupons section, shown for coupon discounts:
  - A table of codes with uses, limit, email, batch and last redeemed, searchable by code and email.
  - Add code: one code typed by hand, with an optional usage limit. This covers the single shared code used today.
  - Generate codes: a quantity, or a list of email addresses pasted or uploaded as CSV, plus prefix, length and uses per code.
  - Export to CSV (code, email, uses, redeemed at), for all codes or one batch.
  - Delete code.

The order timeline shows the `discount-limit-exceeded` activity with a translated label, through `Support\TimelineActivity::description()`.

The index's redemption filter reads `redemption`, and the coupon column shows the code when there is one and the count when there are several. With `discount_redemptions`, the redemptions KPI can count a time window instead of a lifetime total. That is a follow-on, not part of this spec.

### Out of scope

- Pushing codes to email platforms, merge tags, and campaign attribution. Integrations build on `generateCoupons()` and the export.
- Checking the cart's contact email against `coupons.email`. The code is the credential, and single-use codes already limit a forwarded or leaked code to one use. A check would remove the discount mid-checkout when a guest enters their address, and would block recipients who check out with a different address from the one the code was sent to. `email` is recorded, so a per-discount "only the recipient can redeem" condition can be added later without a schema change.
- Reserving a code when payment starts. See alternatives.

## Alternatives considered

- **Keep counting at draft order creation and release unplaced uses later.** A scheduled release runs too late for a shopper retrying within minutes, and it keeps the `consumedDiscountIds()` workaround. Counting at placement removes the need to release anything.
- **Reserve the code when payment starts, and confirm it at placement.** It closes the remaining race completely, but there is no driver-independent "payment started" point in core. It belongs with the cart-phase payment hooks in [[0077-payment-intent-capabilities]] if the event proves not to be enough.
- **Reserve the code when it is applied to the cart.** A guest who moves from phone to laptop would be locked out of their own code until the reservation expires.
- **Use `max_uses_per_user` on one shared code for a campaign.** A per-customer limit stops each customer reusing the code, but anyone who sees the code can still use it once, so it can't restrict an offer to the people it was sent to. A code per recipient can. Per-customer limits for guests are a separate change, [[guest-discount-usage-limits]].
- **Keep one code per discount and duplicate the discount per code.** It works today with no change, but 5,000 discounts per campaign floods the discount index, every cart calculation loads them, and editing the campaign means editing every copy.
- **Leave it to an add-on, and make core extensible with a `CouponResolver` contract.** It is the smallest core change, but the add-on still has to replace usage counting and the order record, as the community implementation shows. Codes as rows is the general model, and a single shared code is the one-row case of it.
- **`cart_id` and `redeemed_at` on the coupon, as in the community implementation.** It fits single-use codes, but a multi-use code has many redemptions, and automatic discounts need a record too for counting at placement to be idempotent.
- **Keep `discount_user` beside `discount_redemptions`.** Both would record a signed-in user's redemption, and they could drift.
- **Hold the order when a limit is exceeded.** It makes the merchant decide before fulfilment, but delays a real customer's order over what is usually a small amount. A listener on `DiscountLimitExceeded` can add a hold for merchants who want one.
- **Tie a code to a `customer_id` and check it at redemption.** It only works when the customer is on the cart, which for a guest is too late or never. `email` records the recipient without blocking guests.
- **A `coupon_batches` table with names and metadata.** The `batch` ULID covers grouping and export. A batches table is an additive change if campaigns need names or notes later.

## Migration impact

- **Database:** new `coupons` and `discount_redemptions` tables; `discount_user` dropped; `discounts.coupon` dropped and `discounts.redemption` added. All folded into the baseline migrations while v2 is in alpha.
- **Breaking changes:**
  - `Discount::$coupon` is gone. Code that reads it uses `$discount->coupons`, and code that writes it creates a `Coupon`.
  - `discounts.uses` counts placed orders, not draft orders. Stores see lower counts for the same traffic, because abandoned checkouts no longer count.
  - `AbstractDiscountType::markAsUsed()`, `Cart::consumedDiscountIds()`, `Cart::forgetConsumedDiscountIds()` and the `$exempt` argument to `Discount::scopeUsable()` are removed.
  - `discount_user` is replaced by `discount_redemptions`. `Discount::users()` keeps its signature.
- **Upgrade from v1.x:** the upgrade package's data migration step:
  - creates a `Coupon` for every v1 discount with a coupon, copying `uses` and `max_uses`, and sets `redemption` to `coupon` for those discounts and `automatic` for the rest;
  - copies each `discount_user` row into `discount_redemptions` with `user_id` and `redeemed_at` from `created_at`, and null `order_id` and `cart_id`.

  The `create_discount_user_table` entry in `packages/upgrade/config/upgrade.php` is replaced. A Rector rule is not practical for `$discount->coupon` reads or `markAsUsed()` overrides, because the replacements change shape. The upgrade notes call them out instead.
- **Config:** `lunar.discounts.coupons.queue_threshold` in `packages/core/config/discounts.php`.
- **Translations:** new keys in `packages/panel/resources/lang/*/discounts.php` for the redemption choice, the Coupons section, generation and export, and in `packages/panel/resources/lang/*/orders.php` for the `discount-limit-exceeded` timeline entry, in all 16 locales. `field_coupon_hint` is removed.
- **Panel:** the discount edit page and index as described above.
- **Docs:** `docs/reference/discounts.mdx` (the `Coupon` and `DiscountRedemption` models, `redemption`, generation, usage counted at placement, and `DiscountLimitExceeded`), `docs/extending/discounts.mdx` (custom types, `$cart->coupon`, and the removal of `markAsUsed()`), and a guide for running a single-use code campaign with guest checkout.

## Open questions

None.

## References

- Community request for per-customer unique discount codes for an email campaign with guest checkout, and the 1.3 implementation quoted above.
- [[0072-panel-discounts-section]], the panel discount screens this extends.
- [[0073-split-amount-off-discount-type]], the previous discount schema change and its upgrade step.
- [[0077-payment-intent-capabilities]], where reserving a code at payment start would live.
- [[guest-discount-usage-limits]], which extends `max_uses_per_user` to guests using `discount_redemptions.email`.

## Implementation plan

- [ ] Slice 1 — Count usage at placement: `discount_redemptions` (including the nullable `email` column) replaces `discount_user`, `RecordDiscountRedemptions` on `OrderPlaced`, atomic counters, `DiscountLimitExceeded` and the order activity, and removal of `markAsUsed()`, `consumedDiscountIds()`, `forgetConsumedDiscountIds()` and the `$exempt` argument. Tests: a declined card then a new cart keeps the use, an abandoned draft doesn't count, `OrderPlaced` firing twice counts once, and an over-limit placement dispatches the event and logs the activity.
- [ ] Slice 2 — Coupons: `coupons` table, `redemption` column, `Coupon` model and factory, `Cart::coupon()`, matching in `DiscountManager`, `AbstractDiscountType` and `CouponValidator`, and the coupon on the order breakdown. Existing behavior covered by moving each single-code test to a one-row coupon, plus guest-cart tests.
- [ ] Slice 3 — Upgrade: v1 `discounts.coupon` → `coupons` and `discount_user` → `discount_redemptions` in the data migration step, and upgrade notes.
- [ ] Slice 4 — Generation: `CouponCodeGenerator`, `CouponBatch`, `GenerateCoupons` action and job, `Discount::generateCoupons()`, and the timing test that sets the `queue_threshold` default.
- [ ] Slice 5 — Panel: the timeline label for `discount-limit-exceeded`, redemption choice, Coupons section, generation by quantity or email list, CSV export, index filter and column, translations in all 16 locales.
- [ ] Slice 6 — Docs, including the guest-checkout campaign guide.
