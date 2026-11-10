# Per-customer discount limits for guest checkout

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-11-10
- TODO item: Per-customer discount limits for guest checkout — count `max_uses_per_user` by contact email as well as user, so guests can use limited coupons without bypassing the limit (spec guest-discount-usage-limits)

## Problem

`max_uses_per_user` only works for signed-in customers, because usage is recorded against a user and nothing else:

- `CreateOrder` calls `AbstractDiscountType::markAsUsed()`, which attaches `$cart->user` to `discount_user` (`user_id` only). A guest order records nothing.
- `AbstractDiscountType::checkDiscountConditions()` rejects a per-user-limited discount when the cart has no user.
- Since #2470 / #2844, `CouponValidator` rejects a per-user-limited coupon for a guest, so the customer is told at entry rather than seeing a coupon that does nothing (#2442).

So a store that sells to guests can't offer a "one per customer" code to them at all. The alternative of skipping the check for guests would make the limit meaningless, because every guest order would be a fresh use. WooCommerce ("Usage limit per user", checked against the billing email) and Shopify ("Limit to one use per customer", checked against the customer's email or phone at checkout) both identify a guest by contact details.

## Proposal

Identify the customer for a per-customer limit by user when there is one, and by normalised contact email otherwise, and record every redemption against both.

### Redemptions

Replace `discount_user` with `discount_redemptions`:

| Column | Type | Notes |
| --- | --- | --- |
| `id` | `id` | |
| `discount_id` | `foreignId` | cascade on delete |
| `order_id` | `foreignId` nullable | the order that consumed the discount; null for rows migrated from `discount_user` |
| `user_id` | user foreign key, nullable | the cart's user, when signed in |
| `email` | `string` nullable, indexed with `discount_id` | the order's contact email, normalised |
| `timestamps` | | |

`markAsUsed()` writes one row with whichever of `user_id` and `email` are known. Recording the email for a signed-in customer too means their earlier guest orders and later signed-in orders count against the same limit.

`Discount::users()` stays as a relation through the new table (`whereNotNull('user_id')`) so existing reads keep working; `Discount::redemptions()` is the new primary relation.

### Counting uses

A new action, `Lunar\Core\Actions\Discounts\CountCustomerRedemptions` (contract `CountsCustomerRedemptions`), owns the question "how many times has this customer used this discount":

```php
public function execute(Discount $discount, ?Authenticatable $user, ?string $email): int;
```

It counts rows matching `user_id = $user` **or** `email = normalise($email)`, so a customer can't reset the limit by signing out. `checkDiscountConditions()` and `CouponValidator` both call it, which removes their current disagreement (`$cart->user` versus `auth()->user()`).

The contact email is the cart's billing address `contact_email`, falling back to the shipping address, matching order mail routing. Normalisation is lower-case and trimmed (see open questions).

### When the email isn't known yet

A guest often enters a coupon before giving an address. The coupon is accepted provisionally: the discount applies while no identity is known, and is checked again at order creation, once the contact email is on the cart.

- `CouponValidator` accepts a per-user-limited coupon for a guest with no contact email yet, and rejects it once the email is known and over the limit.
- `checkDiscountConditions()` applies the discount when no identity is known, and enforces the limit once one is.
- A new order-creation validator (`StepList::OrderCreateValidation`) fails with a translated message when a per-customer limit is exceeded at that point, so the checkout can show "this code has already been used" instead of quietly re-pricing the order.

### Contracts

`CouponValidator::validate(string $coupon)` gains an optional cart: `validate(string $coupon, ?Cart $cart = null): bool`, defaulting to `CartSession::current()`, so the validator can read the contact email. A custom validator that keeps the old signature still satisfies the contract.

## Alternatives considered

- **Skip the limit for guests.** Rejected: a "one per customer" code becomes unlimited for anyone checking out as a guest.
- **Reject guests (the #2470 / #2844 behaviour).** Correct, and the current behaviour, but excludes guests from limited promotions entirely.
- **Count by customer record (`customer_id`).** Guests don't have one, and a signed-in user's customer link is optional, so it doesn't solve the guest case.
- **Keep `discount_user` and add a second `discount_guest` table.** Two tables to count and keep in step; a single redemptions table is simpler and also gives the admin an order-linked usage history.

## Migration impact

- Database: a new `discount_redemptions` table replacing `discount_user`. While v2 is in alpha this folds into the existing baseline (rename the create-table migration); `upgrade` gains a data migration copying v1 `discount_user` rows into `discount_redemptions` with `order_id` and `email` null.
- Public contract: `Discount::users()` keeps working for reads; code that writes `discount_user` directly must move to `redemptions()`. `CouponValidator::validate()` gains an optional parameter. Upgrade guide entry for both.
- Translations: one new validation message, in all 16 locales.
- Admin: the discount's usage view lists redemptions with their order and customer. Panel and Filament.

## Open questions

- **Email normalisation.** Lower-case and trim only, or also strip `+tags` and Gmail dots? Stripping catches more reuse but can merge distinct addresses. Proposed: lower-case and trim, with the normaliser bound in the container so a store can tighten it.
- **Phone numbers.** Shopify also matches on phone. Out of scope unless a store asks.
- **Cancelled and refunded orders.** Should a redemption on a cancelled order still count? `uses` doesn't decrement today. Proposed: keep counting, matching `uses`, and decide separately.
- **Provisional acceptance.** Is applying the discount before the email is known, then failing order creation, acceptable UX, or should storefronts be guided to collect the email before coupons? The checkout guide needs a recommendation either way.

## References

- #2442: the coupon accepted with no effect.
- #2470 (1.x) and #2844 (2.x): reject per-user-limited coupons for guests.
- `packages/core/src/DiscountTypes/AbstractDiscountType.php` (`markAsUsed`, `checkDiscountConditions`, `usesByUser`), `packages/core/src/Validation/CouponValidator.php`, `packages/core/src/Actions/Carts/CreateOrder.php`.

## Implementation plan

- [ ] Slice 1 — `discount_redemptions` table, `Discount::redemptions()`, `markAsUsed()` writes user and email, `Discount::users()` reads through it; upgrade data migration from `discount_user`.
- [ ] Slice 2 — `CountsCustomerRedemptions`, used by `checkDiscountConditions()` and `CouponValidator`; provisional acceptance for guests without an email; the order-creation validator.
- [ ] Slice 3 — admin usage view (panel, Filament), docs and upgrade guide.
