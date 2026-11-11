# Per-customer discount limits for guest checkout

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-11-10
- TODO item: Per-customer discount limits for guest checkout — count `max_uses_per_user` by email as well as user, so guests can use limited discounts without bypassing the limit (spec guest-discount-usage-limits)

## Problem

`max_uses_per_user` only works for signed-in customers, because usage is recorded against a user and nothing else:

- `AbstractDiscountType::markAsUsed()` attaches `$cart->user` to `discount_user` (`user_id` only). A guest order records nothing.
- `AbstractDiscountType::checkDiscountConditions()` rejects a per-user-limited discount when the cart has no user.
- Since #2470 / #2844, `CouponValidator` rejects a per-user-limited coupon for a guest, so the customer is told at entry rather than seeing a coupon that does nothing (#2442).

So a store that sells to guests can't offer a public "one per customer" code to them at all. Skipping the check for guests would make the limit meaningless, because every guest order would be a fresh use. WooCommerce ("Usage limit per user", checked against the billing email) and Shopify ("Limit to one use per customer", checked against the customer's email or phone) both identify a guest by contact details.

Per-recipient codes ([[discount-coupons]]) solve a different problem: restricting an offer to the people it was sent to. They don't help a single code published to everyone.

## Proposal

Identify the customer by user when there is one, and by email otherwise, and count a customer's redemptions by either.

This builds on [[discount-coupons]]: usage is recorded when the order is placed, in `discount_redemptions`, which already carries a nullable `email` column. Nothing here changes that table.

### The customer's email

A new action, `Lunar\Core\Actions\Discounts\ResolveCustomerEmail` (contract `Contracts\Discounts\ResolvesCustomerEmail`), owns the question "which email identifies this cart's customer":

```php
public function execute(Cart $cart): ?string;
```

It returns the first of:

1. the signed-in user's email (`$cart->user`);
2. the billing address `contact_email`;
3. the shipping address `contact_email`.

The result is lower-cased and trimmed. A store that wants stricter matching (stripping `+tags`, Gmail dots) binds its own implementation.

The billing address is the one that identifies the customer for discount limits. A shipping address `contact_email` is for contact about that delivery, and is used only when the billing address has none. Lunar adds no new email field to carts or orders; the redemption row records the email that was counted.

### Recording

`RecordDiscountRedemptions` ([[discount-coupons]]) writes `email` from `ResolvesCustomerEmail` on the order's cart, alongside `user_id`. Recording the email for a signed-in customer too means their earlier guest orders count against the same limit when they used the same email.

### Counting

A new action, `Lunar\Core\Actions\Discounts\CountCustomerRedemptions` (contract `Contracts\Discounts\CountsCustomerRedemptions`), owns the question "how many times has this customer used this discount":

```php
public function execute(Discount $discount, Cart $cart): int;
```

It counts `discount_redemptions` rows for the discount matching `user_id = $cart->user` **or** `email = ResolvesCustomerEmail($cart)`, so a customer can't reset the limit by signing out. It returns `0` when the cart has neither.

### Enforcing the limit

- **Discount conditions.** `checkDiscountConditions()` fails a per-customer-limited discount when `CountsCustomerRedemptions` reaches `max_uses_per_user`. It no longer fails a guest cart for having no user. Because conditions run on every cart calculation, the discount drops off the cart as soon as an email that has used it is known, and the total the customer pays reflects that.
- **Coupon entry.** `CouponValidator::validate(string $coupon, ?Cart $cart = null): bool` takes the cart, defaulting to `CartSession::current()`, and checks the limit through `CountsCustomerRedemptions`. It replaces the guest rejection from #2470 / #2844. A storefront calls it again once the customer gives an email, to tell them the code has already been used. `DiscountManager::validateCoupon()` gains the same optional `?Cart` and passes it through. Both are interface changes, so a custom implementation must add the parameter.
- **Order creation.** A new validator in `StepList::OrderCreateValidation`, `Validation\Cart\ValidateCustomerForDiscounts`, fails when a per-customer-limited discount is applied and the cart has neither a user nor a resolvable email. It does not count redemptions; that is the discount conditions' job. `Cart::canCreateOrder()` runs it, so a storefront learns before taking payment that it needs an email for this discount.
- **Races.** Two orders from the same customer placed at the same moment can both pass the check. `RecordDiscountRedemptions` already dispatches `DiscountLimitExceeded` for the discount-wide and coupon limits; it does the same when a placement takes a customer over `max_uses_per_user`, and never rejects an order that has been paid.

## Alternatives considered

- **Skip the limit for guests.** Rejected: a "one per customer" code becomes unlimited for anyone checking out as a guest.
- **Reject guests (the #2470 / #2844 behaviour).** Correct, and the current behaviour, but excludes guests from limited promotions entirely.
- **A cart-level email column.** It covers a guest who has given an email before any address, but it adds a third email to keep in step with the billing and shipping address emails, and an order has no use for it. The billing address is required to create an order, so the email is known by the time the limit has to hold.
- **Reject an over-limit order at creation.** Some payment drivers create the order after payment has been taken, so throwing there charges the customer and gives them no order. Dropping the discount at calculation and dispatching `DiscountLimitExceeded` at placement avoid that.
- **Count by customer record (`customer_id`).** Guests don't have one, and a signed-in user's customer link is optional.
- **Match on phone as well.** Shopify does. Out of scope until a store asks for it.

## Migration impact

- Database: none beyond [[discount-coupons]], which adds `discount_redemptions.email`.
- Public contract: `Contracts\CouponValidator::validate()` and `Contracts\DiscountManager::validateCoupon()` gain an optional `?Cart` parameter. Callers are unaffected, but PHP rejects an implementation that lacks a parameter its interface declares, so custom implementations must add it: a Rector rule in `upgrade` adds the parameter. `checkDiscountConditions()` now passes guest carts that are within the limit. Two new action contracts in `ActionServiceProvider`. Upgrade guide entry.
- Validators: `ValidateCustomerForDiscounts` is added to the default `lunar.cart.validators.order_create` list.
- Translations: one validation message for `ValidateCustomerForDiscounts`, in all 16 locales.
- Docs: `docs/reference/discounts.mdx` (how a customer is identified for `max_uses_per_user`, and which email counts), and the storefront guidance to re-validate the coupon once an email is known.

## Open questions

- **Cancelled and refunded orders.** Should a redemption on a cancelled order still count? `uses` doesn't decrement today. Proposed: keep counting, matching `uses`, and decide separately.

## References

- [[discount-coupons]]: `discount_redemptions`, counting at placement, `RecordDiscountRedemptions` and `DiscountLimitExceeded`.
- #2442: the coupon accepted with no effect.
- #2470 (1.x) and #2844 (2.x): reject per-user-limited coupons for guests.
- `packages/core/src/DiscountTypes/AbstractDiscountType.php` (`checkDiscountConditions`, `usesByUser`), `packages/core/src/Validation/CouponValidator.php`, `packages/core/src/Validation/Cart/ValidateCartForOrderCreation.php`.

## Implementation plan

Starts after [[discount-coupons]] slice 1.

- [ ] Slice 1 — `ResolvesCustomerEmail` and `CountsCustomerRedemptions`; `RecordDiscountRedemptions` writes `email`; `checkDiscountConditions()` counts through it. Tests: a guest within the limit gets the discount, a guest whose email has used it does not, a signed-in customer's earlier guest order with the same email counts, billing email wins over shipping email.
- [ ] Slice 2 — `CouponValidator` takes the cart; `ValidateCustomerForDiscounts` with its translations; `DiscountLimitExceeded` for per-customer limits at placement.
- [ ] Slice 3 — Docs and upgrade guide.
