# 0014 — Price calculator service

- Status: draft
- Author: Glenn Jacobs
- Created: 2026-05-26
- TODO item: (follow-up to 0012)

## Problem

Spec 0012 took the per-attribute object allocation out of money columns. What it deliberately did not touch is the arithmetic that the cart pipelines, tax driver, and discount types do with the resulting integers. That arithmetic is currently scattered, inconsistent, and untested as a system.

Concrete examples in the v2 codebase as of this spec:

- **Rounding strategy is implicit.** `Lunar\Core\DiscountTypes\AmountOff::applyFixedValue` uses `floor()` when distributing a fixed discount across lines (so the discount never overshoots the budget), then re-allocates the remainder line-by-line. `AmountOff::applyPercentage` uses `round()` per line. Both are correct *individually* but the choice is implicit — a reader has to guess the intent from the surrounding code.
- **Multi-rate tax-inclusive stripping has bespoke balancing logic.** `Lunar\Core\Drivers\SystemTaxDriver::getBreakdown` (the `prices_inc_tax()` branch, ~lines 133-152) manually subtracts the running tax tally from the original on the last line item so the breakdown sums to the input. This is real, careful work — and it lives inline in the driver, untested in isolation, and gets re-derived from scratch by any custom tax driver a consumer writes.
- **Tax round-trip isn't guaranteed.** `Lunar\Core\Models\Price::priceExTax` does `(int) round($value / (1 + $rate))` and `priceIncTax` does `(int) round($value * (1 + $rate))`. The two are not strict inverses — `priceExTax(priceIncTax(x))` can drift by 1 minor unit. No test covers the round-trip; no caller knows whether they're seeing a real value or a rounding artefact.
- **Major↔minor conversion is duplicated.** `(int) round($decimal * $currency->factor)` appears in `AmountOff`, `ShippingDiscount`, `ManageProductPricing`, `PriceRelationManager`, `ManageShippingRates`. `bcmul((string) $amount, (string) $order->currency->factor)` appears in `RefundOrder`. Whether bc-math is needed depends on the currency's `factor` and the platform's float behaviour — there's no rule, and the two approaches can disagree at the last minor unit on currencies with three or four decimal places.
- **No currency mismatch guard.** Every arithmetic site assumes the two ints share a currency. Nothing enforces it. A cart pipeline that accidentally mixes a `$cart->discountTotal` (in GBP) with a `$shippingTaxTotal` (in EUR, e.g. via a misconfigured shipping driver) silently produces a wrong number.

Net effect: rounding and precision policy for the whole store is encoded as folklore across half a dozen files. A consumer that needs banker's rounding, or a different inc-tax stripping strategy, has to subclass the discount type **and** the tax driver **and** any custom pipelines, and discover by trial which `round()` calls matter. Spec 0012 took out a hot allocation cost; this spec takes out the silent correctness cost.

## Proposal

Introduce a swappable, container-bound calculator service that owns the standard money operations, then route the existing pipelines / drivers / discount types through it. Storage stays integer minor units. `PriceValue` stays a thin currency-aware display wrapper at boundaries — it does not grow operations.

### A. The service

```php
namespace Lunar\Core\Pricing;

use Lunar\Core\Models\Contracts\Currency;

interface PriceCalculatorInterface
{
    /**
     * Multiply a minor-unit integer by a rate (e.g. 0.20 for 20%) and round
     * to whole minor units. Used for tax and percentage-based discounts.
     */
    public function percentage(int $value, float $rate, Currency $currency): int;

    /**
     * Add tax to a tax-exclusive value. `$rate` is a decimal (0.20 = 20%).
     */
    public function withTax(int $value, float $rate, Currency $currency): int;

    /**
     * Strip tax from a tax-inclusive value. Inverse of `withTax` to the
     * minor unit; `withTax(withoutTax($v, $r), $r) === $v` is a guarantee.
     */
    public function withoutTax(int $value, float $rate, Currency $currency): int;

    /**
     * Distribute a total across N weighted parts so the parts sum back to
     * the total exactly (no rounding drift). Used for fixed-value discount
     * allocation and multi-rate tax breakdown balancing.
     *
     * @param  array<int|string, int>  $weights  positive integer weights
     * @return array<int|string, int>  values that sum to $total
     */
    public function distribute(int $total, array $weights, Currency $currency): array;

    /**
     * Convert a major-unit decimal (e.g. `12.99`) to minor units using
     * the currency's factor, rounding to whole minor units.
     */
    public function toMinor(float|string $major, Currency $currency): int;

    /**
     * Convert a minor-unit integer to a major-unit decimal.
     */
    public function toMajor(int $minor, Currency $currency): float;
}
```

`Lunar\Core\Pricing\DefaultPriceCalculator implements PriceCalculatorInterface` ships the standard implementation:

- `percentage` / `withTax` / `withoutTax` use half-up rounding (PHP's `round()` default), respecting `$currency->decimal_places`.
- `withoutTax` is implemented as `intdiv($value * $denom, $denom + intRate)` style integer math (or `bcdiv` for currencies with `decimal_places >= 4`) to guarantee the round-trip invariant. The invariant is enforced by a test.
- `distribute` uses largest-remainder allocation: floor each `$total * $weight / sum($weights)`, then hand out the remainder to the lines with the largest fractional parts. This generalises the AmountOff "remainder pass" and the SystemTaxDriver "subtract from the last item" logic.
- `toMinor` uses `bcmul` when `$currency->decimal_places >= 4`, plain float math otherwise. Cutover point lives on the implementation, not the interface — consumers can subclass to change it.

Binding in `Lunar\Core\LunarServiceProvider::register()`:

```php
$this->app->singleton(PriceCalculatorInterface::class, function ($app) {
    return $app->make(
        config('lunar.pricing.calculator', DefaultPriceCalculator::class)
    );
});
```

`config/lunar.php` gains a `pricing.calculator` key alongside the existing `pricing.formatter`. Both follow the same swap pattern.

### B. Migration of call sites

For every site below, replace inline arithmetic with calls to the calculator (resolved once per pipeline pass via `app(PriceCalculatorInterface::class)`, then passed around).

| File | Current | After |
| --- | --- | --- |
| `Models/Price::priceExTax` | `(int) round($value / (1 + $rate))` | `$calc->withoutTax($value, $rate, $currency)` |
| `Models/Price::priceIncTax` | `(int) round($value * (1 + $rate))` | `$calc->withTax($value, $rate, $currency)` |
| `Models/Price::comparePriceIncTax` | same shape | `$calc->withTax(...)` |
| `Drivers/SystemTaxDriver::getBreakdown` (exc-tax branch) | `round($subTotal * ($pct / 100))` per amount | `$calc->percentage($subTotal, $pct / 100, $currency)` |
| `Drivers/SystemTaxDriver::getBreakdown` (inc-tax branch) | bespoke "subtract from last item" balancing | `$calc->distribute($expectedTax, $weights, $currency)` |
| `Pipelines/Cart/CalculateTax` | shipping tax `round(...)`s | `$calc->percentage(...)` |
| `DiscountTypes/AmountOff::applyPercentage` | `(int) round($subTotal * ($value / 100))` | `$calc->percentage($subTotal, $value / 100, $currency)` |
| `DiscountTypes/AmountOff::applyFixedValue` | `floor($subTotal * $divisional)` + manual remainder loop | `$calc->distribute($value, $lineSubtotals, $currency)` |
| `DiscountTypes/BuyXGetY` | `$unitPrice * $qtyToAllocate` (no rounding, but lives here for symmetry) | calculator helper (or leave; pure integer mul has no rounding question) |
| `table-rate-shipping/DiscountTypes/ShippingDiscount` | `(int) round($value * (1 - $pct / 100))` | `$calc->percentage(...)` (then subtract) |
| `Actions/Orders/RefundOrder` | `(int) bcmul((string) $amount, (string) $factor)` | `$calc->toMinor($amount, $currency)` |
| `admin/.../ManageProductPricing`, `PriceRelationManager`, `ManageShippingRates`, `ShippingDiscount::lunarPanelOnSave` | major→minor via `(int) round($decimal * $factor)` | `$calc->toMinor($decimal, $currency)` |
| `admin/.../ManagesProductPricing`, same RMs (form fill side) | minor→major via `$value / $factor` | `$calc->toMajor($value, $currency)` |

`PriceValue` stays untouched. The calculator takes ints and returns ints; callers wrap into `PriceValue` only at the point where they cross a boundary that needs formatting.

### C. Currency-mismatch guard (optional, off by default)

`DefaultPriceCalculator` does not currently take two values to add/multiply — the operations are all "one value + a rate/weight/factor + a currency". So there's no place to assert currency equality. If a future operation (`add(PriceValue, PriceValue): PriceValue`) gets added, it asserts equal currencies and throws `MismatchedCurrencyException` on violation.

Out of scope for this spec — but called out so future additions land in the right place.

## Alternatives considered

- **Static helper (`PriceCalculator::percentage(...)` etc.).** Cheaper to call, no container resolution per pipeline pass. Rejected because the explicit driver of this spec is *swappability* — a merchant who needs banker's rounding or a different inc-tax stripping strategy must be able to override one implementation, not monkey-patch a static. Container resolution is once per pipeline pass; the arithmetic itself is hot but a method call on a singleton is not.
- **Move the operations onto `PriceValue`.** Considered. Rejected because `PriceValue` is read on every Eloquent attribute access touched by the trait — putting arithmetic on it tempts callers to allocate `PriceValue` per operation, defeating spec 0012. Keeping `PriceValue` thin and routing operations through an injected service preserves the separation: storage = int, display = PriceValue, math = calculator.
- **Adopt `moneyphp/money`.** Industry standard, well-tested, supports BCD and ISO 4217 out of the box. Rejected for v2 because (1) it forces every cart pipeline allocation back, undoing 0012; (2) its rounding modes don't map cleanly onto Lunar's `Currency::decimal_places` config; (3) it's a hard public-contract addition we'd have to expose forever. The interface in this spec is small enough that a consumer who *wants* `moneyphp/money` can bind their own `PriceCalculatorInterface` implementation that delegates to it.
- **Do nothing.** The bugs are latent rather than reported. But (a) merchants on currencies with `decimal_places >= 3` (BHD, KWD, JOD) are most exposed to the tax-round-trip drift, (b) the bespoke balancing logic in `SystemTaxDriver` is duplicated implicitly across any custom driver a consumer ships, and (c) the lack of a documented rounding strategy is the kind of thing that surfaces as a support ticket once enough merchants are on v2. Better to land the seam while we're already touching the pricing layer.

## Migration impact

- **Database migrations**: none.
- **Public contract surface**: net-additive. `PriceCalculatorInterface` + `DefaultPriceCalculator` are new. No existing class signature changes; the migrated call sites are internal. A consumer who has subclassed `SystemTaxDriver` or `AmountOff` to override the rounding behaviour will find the inline arithmetic gone — but the override seam moves up to `PriceCalculatorInterface`, which is a better surface to subclass anyway. The Upgrade package documents the substitution.
- **Behavioural change for merchants**: `withoutTax(withTax($x))` is now a strict round-trip; previously it could drift by 1 minor unit on certain rates. This is a fix, not a regression — but it will change the displayed value on a small number of catalogue prices for stores on `prices_inc_tax`. Worth a CHANGELOG callout and a benchmark comparison in the PR.
- **Upgrade path for v1.x consumers**: no Rector rule needed — v1 had no equivalent concept. The Upgrade package's notes section gains a paragraph telling subclassers of `SystemTaxDriver` / `AmountOff` to bind a `PriceCalculatorInterface` instead.
- **Translation / locale impact**: none.
- **Filament / admin impact**: the four major↔minor conversion sites in published / bridge resources (`ManageProductPricing`, `PriceRelationManager`, `ManageShippingRates`, `ShippingDiscount::lunarPanelOnSave`) become one-liners. Published copies (via spec 0010) keep working until they update — the old `(int) round(...)` still produces the right number for currencies with 2 decimal places.

## Open questions

- **Rounding strategy default.** The spec proposes half-up (PHP's `round()` default) to preserve current behaviour exactly. Banker's rounding (`PHP_ROUND_HALF_EVEN`) is mathematically friendlier for accounting but would change every existing cart total. Decision: keep half-up as the default, expose `PHP_ROUND_HALF_EVEN` as a one-line override consumers can bind via subclass. Owner: implementer.
- **`distribute()` weighting under negative or zero weights.** What should `distribute(100, [50, 0, 50], $currency)` return? Probably `[50, 0, 50]` (zero-weight lines get zero); negative weights should throw. Worth deciding in the implementation PR and pinning with tests.
- **Benchmark vs the pre-0012 baseline.** Worth pairing the PR with a micro-benchmark showing the calculator overhead per pipeline pass is dominated by what 0012 saved per attribute access. Not blocking the spec; useful for the PR description.

## Sequencing

This spec lands **after** [[0013-base-directory-reorganisation]]. 0013 moves `Lunar\Core\Base\Traits\HasPrices` to `Lunar\Core\Models\Concerns\HasPrices`, which several of the inline arithmetic sites this spec consolidates currently live alongside. Reordering avoids merge churn on the trait and lets this spec land against the post-relocation namespacing directly.

## References

- Spec [[0012-price-data-type-refactor]] — laid down the `PriceValue` boundary and the `'integer'` cast; this spec builds on top.
- Spec [[0013-base-directory-reorganisation]] — relocates `HasPrices` and other `Base/` residents this spec consumes; lands first.
- Existing service: `Lunar\Core\Pricing\PriceFormatterInterface` / `Lunar\Core\Pricing\DefaultPriceFormatter` — the naming and binding pattern this spec mirrors.
- Inline arithmetic sites enumerated above; see grep for `(int) round(.*factor)` and `round(.*\/.*(1 \+`.
