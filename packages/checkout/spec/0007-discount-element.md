# 0007 — Checkout Elements: discount element

- Status: draft
- Author: Alec Ritson
- Created: 2026-06-08
- TODO item: "Checkout Elements — discount/coupon element (resolves 0001 open question)"

## Problem

[[0001-core-element-model]] left one element unresolved: a coupon/discount field is registered in
its examples (`Checkout::add(DiscountElement::class)->region(CheckoutRegion::Summary)`) and appears
in the [[0006-worked-example]] layout (`summary: ["discount"]`), but its open question — **"discount
as a `Summary`-region element vs a summary-component property"** — was never decided and the element
was never specified. Every other built-in element (contact, address, shipping options, payment host)
has a defined capability set and persistence path; the discount field does not, so consumers have no
contract for applying or removing a coupon during checkout.

There is also a concrete surface gap: unlike shipping and addresses, **Lunar v2 has no
`Cart::setCoupon()` cart-API method**. A coupon is a single `coupon_code` property on the cart
(cast `CouponString`), validated by `DiscountManager::validateCoupon()` and applied during
`DiscountManager::apply()` on recalculation. The element model's "persist through the cart API, never
a direct model write" invariant ([[0001-core-element-model]] §D) therefore has nothing to call.

## Proposal

**Resolve the open question: a discount is an element**, not a summary-component property — so it
goes through the same capability contract, validation gate, and DTO as every other element, and a
headless client gets a first-class coupon affordance instead of a bespoke summary field. It is a
`CapturesData` element placed in the `Summary` region, capturing a **single** coupon code (matching
core's one-`coupon_code`-per-cart model), supporting apply and remove.

The cart-API invariant is preserved by the **driver**: the Lunar checkout driver's coupon verbs
own the mutation internally — validate via the discount manager, assign, recalculate (§B) — so
the element never writes the model directly. A thin core **`Cart::setCoupon(?string $code)`** is
an independent nice-to-have the driver could later delegate to; checkout does **not** block
on it.

### A. The element

A `DiscountElement` in `lunar/checkout`, implementing the required core plus `CapturesData` and
`ReportsCompletion` ([[0001-core-element-model]] §B):

```php
namespace Lunar\Checkout\Elements;

use Lunar\Checkout\AbstractCheckoutElement;
use Lunar\Checkout\Contracts\CapturesData;
use Lunar\Checkout\Contracts\ReportsCompletion;

class DiscountElement extends AbstractCheckoutElement implements CapturesData, ReportsCompletion
{
    public function handle(): string    { return 'discount'; }
    public function title(): string     { return 'lunar::checkout.elements.discount.title'; }
    public function component(): string { return 'discount'; } // rendering hint

    public function mount(): void { /* read-only; nothing to seed (0001 §E) */ }

    public function data(): array
    {
        return ['coupon_code' => $this->context->getCoupon()];
    }

    /** STATIC rules. The coupon's *validity* (exists, in date, not used up) is NOT a static rule —
     *  it is checked in store() via DiscountManager and surfaced as a validation failure. (0001 §H) */
    public function rules(): array
    {
        return ['coupon_code' => ['nullable', 'string', 'max:255']];
    }

    public function validationMessages(): array
    {
        return ['coupon_code.string' => __('lunar::checkout.elements.discount.invalid')];
    }

    public function validate(array $data): void
    {
        \Illuminate\Support\Facades\Validator::make($data, $this->rules(), $this->validationMessages())->validate();
    }

    /** Persists THROUGH the context's typed write verb (0001 §D): the session-backed flow routes
     *  to the driver's applyCoupon/removeCoupon (re-syncing the session, 0010 §B); the Lunar
     *  driver owns the mutation internally — validate via DiscountManager::validateCoupon(),
     *  assign, recalculate (§B). An empty/absent code clears the coupon (remove). */
    public function store(array $data): void
    {
        $this->context->applyCoupon($data['coupon_code'] ?? null);
    }

    /** A discount is optional — its presence never blocks placement. (0001 §F) */
    public function required(): bool { return false; }

    /** Complete when there is no coupon OR a coupon that is currently applied & valid.
     *  "Set but rejected" is not complete, but since required() is false it never blocks Gate 2. */
    public function isComplete(): bool
    {
        $code = $this->context->getCoupon();

        return $code === null || $this->context->snapshot()->hasAppliedDiscount;
    }
}
```

(`data()` / `isComplete()` read through the **context's read surface** — `getCoupon()` and
`CartSnapshot::hasAppliedDiscount` via `snapshot()`, mirroring the driver read verbs
([[0010-cart-session-reconciliation]] §B) — never `$this->context->cart->…`. The element has no
cart model to touch ([[0001-core-element-model]] §C); the same reads serve both flows. The
context maps `applyCoupon(null)` to the driver's `removeCoupon(CheckoutSession)` — no code
argument — and a non-null code to `applyCoupon(CheckoutSession, string $code)`
([[0010-cart-session-reconciliation]] §B).)

It is placed in the `Summary` region and is `required(false)`, so it renders alongside the order
summary and never gates the order:

```php
Checkout::add(DiscountElement::class)->region(CheckoutRegion::Summary);
```

### B. Coupon mutation is driver-owned; `Cart::setCoupon()` is optional core sugar

The element's `store()` calls the context's `applyCoupon(?string)`, which routes to the checkout
driver's coupon verbs ([[0010-cart-session-reconciliation]] §B): `applyCoupon(CheckoutSession,
string $code)` for a non-null code, `removeCoupon(CheckoutSession)` (no code argument) for
`null`. The **Lunar checkout driver owns the mutation internally** — validate via
`DiscountManager::validateCoupon()`, assign `coupon_code` (the `CouponString` cast normalises;
`null` clears), recalculate — so the no-direct-write invariant holds without the element (or any
transport) ever touching a cart model. An invalid code throws a transport-neutral
`ValidationException`, projected by each adapter ([[0003-transport-projections]] §H). The element
body stays a one-liner; the driver owns validation + recalc.

A thin **`Cart::setCoupon(?string $code, bool $refresh = true): Cart`** on
`Lunar\Core\Models\Cart` — the coupon analogue of `setShippingOption()`, same `bool $refresh`
tail, same recalc contract — remains a **nice-to-have core improvement that lands
independently**: useful outside checkout, and a method the Lunar driver could delegate to once it
exists. Checkout does **not** block on it; nothing in this spec requires a core change.

### C. Transport projection

No new transport surface — the element flows through the existing coarse cart write the projections
already name: `POST /checkout/{uuid}/coupons` ([[0003-transport-projections]] §A) and the Inertia
`/checkout/elements` multiplexer with `{handle: "discount", coupon_code: "..."}`. An empty
`coupon_code` removes the coupon. A rejected coupon renders to the Inertia error bag / JSON:API
`errors[]` exactly like any other Gate-1 failure. Because a coupon changes totals, the recomputed
`CheckoutData` returned in the same response reflects the new summary.

```vue
<!-- Discount.vue (lunar/checkout) — skeleton -->
<script setup lang="ts">
import { ref } from 'vue'
import { useCheckout } from '@lunarphp/checkout'
import type { CheckoutElementData } from '@/types/generated'

const props = defineProps<{ element: CheckoutElementData | undefined }>()
const { store } = useCheckout()
const code = ref(props.element?.data.coupon_code ?? '')

const apply  = () => store('discount', { coupon_code: code.value })
const remove = () => { code.value = ''; store('discount', { coupon_code: '' }) }
</script>
```

## Alternatives considered

- **Summary-component property, not an element.** Rejected (the resolved open question): a headless
  client would have no discoverable coupon affordance, validation/error rendering would be special-
  cased outside the element pipeline, and the summary component would own write logic — the exact
  drift the element model prevents.
- **Write `coupon_code` directly + `calculate()` from the element.** Rejected: violates
  [[0001-core-element-model]] §D (no direct model writes). The driver-owned coupon verbs (§B)
  keep every element persisting through one sanctioned surface.
- **Multiple coupons / a coupon collection element.** Rejected for now: core models a single
  `coupon_code` per cart; a multi-coupon element would front a capability core does not have. Deferred
  until/if core supports stacked coupons.
- **Validity as a `rules()` constraint.** Rejected: `rules()` are static and must not branch on
  persisted state or hit the discount engine ([[0001-core-element-model]] §H); coupon validity is a
  `store()`-time check via `DiscountManager::validateCoupon()`.

## Migration impact

- **No required core change.** Coupon mutation is owned by the Lunar checkout driver (§B). A thin
  `Cart::setCoupon(?string, bool $refresh = true): Cart` in `lunar/core` — additive, mirrors
  `setShippingOption()` — is an **optional, independent** improvement; if/when it lands it is new
  public surface and a future breaking change needs a Rector rule per the package rule.
- New `DiscountElement` in `lunar/checkout` + its `Discount.vue` (registry key `discount`).
- No schema change — `carts.coupon_code` already exists.
- 16-locale keys: `lunar::checkout.elements.discount.{title,invalid,unavailable}`.
- Resolves the [[0001-core-element-model]] open question; that spec's open-questions list drops the
  discount line and references this spec.

## Acceptance checks

- A valid coupon applied via the element recalculates the cart (discount appears in totals) and the
  element reports `isComplete()`; an invalid/expired coupon is rejected at Gate 1, rolls back, and
  renders an error — without ever blocking placement (`required()` is false).
- An empty `coupon_code` removes an applied coupon and recalculates.
- The driver's `applyCoupon` validates via `DiscountManager::validateCoupon()`, throws a
  `ValidationException` on an invalid code, and recalculates on success; `applyCoupon(null)` on
  the context maps to the driver's `removeCoupon`; a direct `coupon_code` model write is not used
  anywhere in the element or its transports.
- The element projects through `POST /checkout/{uuid}/coupons` (REST) and the `/checkout/elements`
  multiplexer (Inertia) with no transport-specific element code.
- `ArchitectureTest`: `DiscountElement` extends `AbstractCheckoutElement` and declares `handle()`;
  PHPStan level 0 + Pint pass.

## Open questions

- **Resolved** (§B): the Lunar checkout driver owns coupon mutation internally; `Cart::setCoupon()`
  is an independent nice-to-have core improvement and checkout does not block on it.
- Free-shipping / auto-applied discounts that need no code: surfaced read-only in the summary, or a
  separate display-only element? (Owner: design.)
- Stacked/multiple coupons — deferred until core models more than one `coupon_code`.

## References

- [[0000-overview]], [[0001-core-element-model]] (§B capabilities, §D cart-API writes, §F completeness,
  §H validation), [[0003-transport-projections]] (§A `/coupons`, §H error projection), [[0006-worked-example]].
- Real Lunar v2 surfaces: `Lunar\Core\Models\Cart::$coupon_code` (`CouponString` cast),
  `Lunar\Core\Contracts\DiscountManager::validateCoupon(string): bool`,
  `Lunar\Core\Managers\DiscountManager::apply(Cart): Cart`.
