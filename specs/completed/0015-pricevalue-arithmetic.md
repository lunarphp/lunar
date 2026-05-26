# 0015 — PriceValue arithmetic

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-26
- TODO item: (follow-up to 0014)

## Problem

Spec 0014 landed a swappable `PriceCalculatorInterface` for rounding-sensitive money operations (percentage, tax round-trip, distribution, major↔minor conversion) and routed every site that does *rounding* arithmetic through it. What 0014 deliberately did not address is the rest of the money math — plain additions, subtractions, and aggregations of two integer values that share a currency. Those are still inline raw-int arithmetic on `PriceValue->value`.

Concrete examples in the v2 codebase as of this spec:

- **Subtraction is inline raw-int.** `Lunar\Core\Actions\Carts\CalculateLine::handle` computes `$subTotal = $cartLine->subTotal->value - $cartLine->discountTotal->value`. The two operands are `PriceValue`s with currencies — the currencies should be identical, but nothing enforces it.
- **`AmountOff` and `BuyXGetY` rebuild PriceValues from raw int subtraction.** `new PriceValue($line->subTotal->value - $amount, $currency)` appears in `DiscountTypes/AmountOff.php` and `DiscountTypes/BuyXGetY.php`. Each is a "subtract two integers, wrap back into a value object" that re-passes `$cart->currency` by hand.
- **Aggregation uses `Collection::sum('price.value')`.** `Pipelines/Cart/CalculateTax` and the cart breakdown reducers sum `taxBreakdownAmounts->sum('price.value')`, `shippingBreakdown->items->sum('price.value')`, etc. The sum is an integer; the result is then re-wrapped: `new PriceValue($amounts->sum('price.value'), $cart->currency)`. Currency mismatch within the collection (e.g. a stale shipping breakdown carrying a different currency) is silently ignored.
- **No currency-mismatch guard anywhere.** Every arithmetic site assumes the two ints share a currency. Spec 0014 called this out as out-of-scope — "If a future operation (`add(PriceValue, PriceValue): PriceValue`) gets added, it asserts equal currencies and throws `MismatchedCurrencyException` on violation."
- **Two construction styles diverge in intent.** `new PriceValue($a + $b, $currency)` and `new PriceValue($total, $currency)` look identical to a reader, but the first is *aggregating two currencied things* and the second is *boxing a known total*. There's no way to distinguish "I'm trying to add two prices" from "I have a final integer answer" in code review.

Net effect: spec 0014 fixed the *correctness* of rounding-sensitive operations, but the rest of the pipeline still does raw-int arithmetic on currencied values without a guard. A consumer (or future Lunar contributor) can accidentally combine a `$cart->discountTotal` (GBP) with a `$shippingTaxTotal` (EUR, via a misconfigured driver or a stale value object) and silently produce a wrong number. Spec 0014 made the rounding policy explicit; this spec makes the currency invariant explicit.

## Proposal

Add arithmetic and comparison operations directly to `PriceValue` as currency-aware methods that return new `PriceValue` instances and assert currency equality on every binary op. Then route every inline `->value + ->value` / `->value - ->value` / `new PriceValue($a + $b, $currency)` site through them.

`PriceValue` stays an immutable value object (the `readonly` properties from spec 0012 are preserved). Operations return *new* `PriceValue`s; nothing mutates.

### A. The new methods

```php
namespace Lunar\Core\DataObjects;

use Lunar\Core\Exceptions\MismatchedCurrencyException;

final class PriceValue implements HasCurrency
{
    // ... existing constructor and methods stay ...

    /**
     * Add another PriceValue. Throws if currencies differ.
     */
    public function add(PriceValue $other): PriceValue
    {
        $this->assertSameCurrency($other);

        return new PriceValue($this->value + $other->value, $this->currency);
    }

    /**
     * Subtract another PriceValue. Throws if currencies differ.
     * Result may be negative (refund / over-discount scenarios).
     */
    public function subtract(PriceValue $other): PriceValue
    {
        $this->assertSameCurrency($other);

        return new PriceValue($this->value - $other->value, $this->currency);
    }

    /**
     * Multiply this value by an integer scalar (e.g. line quantity).
     * No rounding question — integer * integer is exact.
     */
    public function multiply(int $multiplier): PriceValue
    {
        return new PriceValue($this->value * $multiplier, $this->currency);
    }

    /**
     * Clamp the value to a minimum of zero. Useful after subtractions
     * that may have over-shot (e.g. discount > subtotal edge cases).
     */
    public function clampToZero(): PriceValue
    {
        return $this->value < 0
            ? new PriceValue(0, $this->currency)
            : $this;
    }

    /**
     * Equality on (value, currency).
     */
    public function equals(PriceValue $other): bool
    {
        return $this->currency->getKey() === $other->currency->getKey()
            && $this->value === $other->value;
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }

    public function isNegative(): bool
    {
        return $this->value < 0;
    }

    /**
     * Static aggregate. Sums an iterable of PriceValues. All entries
     * must share `$currency` or {@see MismatchedCurrencyException} is
     * thrown. Returns `new PriceValue(0, $currency)` for an empty input
     * — the caller passes the currency explicitly so empty-collection
     * cases can't silently fall through.
     *
     * @param  iterable<PriceValue>  $values
     */
    public static function sum(iterable $values, Currency $currency): PriceValue
    {
        $total = 0;

        foreach ($values as $value) {
            if ($value->currency->getKey() !== $currency->getKey()) {
                throw new MismatchedCurrencyException(
                    "Cannot sum PriceValue in {$value->currency->code} into a {$currency->code} total."
                );
            }
            $total += $value->value;
        }

        return new PriceValue($total, $currency);
    }

    private function assertSameCurrency(PriceValue $other): void
    {
        if ($this->currency->getKey() !== $other->currency->getKey()) {
            throw new MismatchedCurrencyException(
                "Currency mismatch: {$this->currency->code} vs {$other->currency->code}."
            );
        }
    }
}
```

`Lunar\Core\Exceptions\MismatchedCurrencyException` is a new domain exception extending `\DomainException` (consistent with the rest of `Lunar\Core\Exceptions`).

### B. Why on `PriceValue`, not on `PriceCalculatorInterface`

Spec 0014's calculator deliberately stayed at "one value + a rate/weight/factor + a currency" because the rounding-sensitive ops it owns don't have two-value-with-currency inputs. The currency-mismatch guard requires both operands to *carry* their currency — i.e. to be `PriceValue`s, not ints. So this work has to land on the value object, not on the calculator.

The calculator and `PriceValue` then divide responsibility cleanly:
- **Calculator**: rounding-sensitive operations on ints with a known currency context. Swappable per implementation (banker's rounding, custom bc-math cutover).
- **PriceValue**: currency-safe combination of two currencied values. The mismatch guard is *not* overridable — it's a correctness invariant of the value object, not a strategy.

### C. Performance: preserving the spec 0012 win

Spec 0012 took out the per-attribute object allocation by making `PriceValue` an integer cast at the Eloquent boundary, not an object accessed per attribute read. This spec re-introduces `PriceValue → PriceValue` operations, which allocate.

The mitigation is that these ops appear in *cart pipeline math*, not *per-attribute reads*. A cart with 50 lines does maybe 10 `add` / `subtract` calls in `CalculateLine` and `CalculateTax`, plus one `PriceValue::sum()` per breakdown. That's ~hundreds of allocations per cart calculation — orders of magnitude below the per-attribute path 0012 saved.

The PR should pair with a micro-benchmark on `Cart::calculate()` for a 50-line cart to confirm. The expected result is a single-digit-percentage hit on cart calc; not a regression of 0012's per-read savings.

If the benchmark surprises us, the fallback is to keep `->add()` / `->subtract()` but inline the static `PriceValue::sum()` (sum the raw `value`s, single allocation at the end). That preserves the call-site readability while halving allocations on the hot path.

### D. Migration of call sites

For every site below, replace inline arithmetic with the corresponding method call. The grep targets are documented so a follow-up audit can verify completeness.

| File | Current | After |
| --- | --- | --- |
| `Actions/Carts/CalculateLine` | `$subTotal = $cartLine->subTotal->value - $cartLine->discountTotal->value` | `$cartLine->subTotal->subtract($cartLine->discountTotal)` |
| `DiscountTypes/AmountOff::applyFixedValue` | `new PriceValue($line->subTotal->value - $amount, $currency)` (×2) | `$line->subTotal->subtract($discountValue)` |
| `DiscountTypes/AmountOff::applyPercentage` | `new PriceValue($subTotal - $amount, $currency)` | `subtract($discountValue)` |
| `DiscountTypes/BuyXGetY` (line 328 et al) | `max(0, $rewardLine->subTotal->value - $rewardLine->discountTotal->value)` | `$rewardLine->subTotal->subtract($rewardLine->discountTotal)->clampToZero()` |
| `Pipelines/Cart/CalculateTax` | `new PriceValue($amounts->sum('price.value'), $cart->currency)` (×N — tax breakdown, shipping tax total, line total) | `PriceValue::sum($amounts->pluck('price'), $cart->currency)` |
| `Pipelines/Cart/CalculateLine` shipping total assembly | `new PriceValue($shippingSubTotal + $shippingTaxTotal->value, $currency)` | `$shippingSubTotalPriceValue->add($shippingTaxTotal)` |
| `ValueObjects/Cart/TaxBreakdown` / `ShippingBreakdown` aggregation helpers | `$collection->sum('price.value')` | `PriceValue::sum($collection->pluck('price'), $currency)` |
| Cart line `total` / `subTotalDiscounted` / `discountTotal` mutations | various `new PriceValue(int math, $currency)` | the appropriate `add` / `subtract` / `clampToZero` chain |

A scan of the codebase found 27 `->value` arithmetic sites and 67 `new PriceValue(...)` constructions; the PR's job is to triage each — most should migrate, a few legitimately box a final int answer (e.g. constructors at storage boundaries) and stay as-is.

### E. Currency on the value object: making `$currency` readable

`PriceValue::$currency` is currently `private readonly`. The `assertSameCurrency` guard needs it to be readable from another `PriceValue` instance — same-class access works (PHP's visibility is class-level, not instance-level), so no API change is needed. External callers continue to use `resolveCurrency()`.

## Alternatives considered

- **Operator overloading via a userland library (e.g. PHP's `__toString` + arithmetic operators).** PHP doesn't support operator overloading. The `+` operator on objects would just throw. Not an option.
- **Move all math to `PriceCalculator` with two-value methods (`$calc->add(PriceValue $a, PriceValue $b): PriceValue`).** Considered. Rejected because (1) the calculator's purpose per spec 0014 is *swappable rounding strategy*, and currency-mismatch is *not* a strategy — every implementation must enforce it; (2) routing `$a->add($b)` through `$calc->add($a, $b)` adds a container resolution per op for no gain; (3) the method-on-the-value-object form reads better at call sites (`$subTotal->subtract($discount)` vs `app(PriceCalculatorInterface::class)->subtract($subTotal, $discount)`).
- **Make `PriceValue` mutable (set `$value` in-place).** Rejected outright. The `readonly` properties from spec 0012 are load-bearing for the Eloquent integer-cast boundary — mutation would break the assumption that a `PriceValue` retrieved from a cast is a stable snapshot.
- **Wait until a bug forces it.** The bugs are latent rather than reported. But (a) the work to migrate ~94 sites is mechanical and easier to land in one focused PR than as drive-by changes; (b) spec 0014's section C explicitly leaves a marker for this work; (c) every new feature that touches the cart pipeline currently has to reinvent which `->value` to subtract — landing the seam stops that drift.
- **`moneyphp/money` (again).** Same rejection as spec 0014 — forces an allocation per cast and is over-shaped for v2's needs. Consumers who want it can subclass `PriceValue` and delegate.

## Migration impact

- **Database migrations**: none.
- **Public contract surface**: net-additive. New methods on `PriceValue`, new `MismatchedCurrencyException`. No existing method signatures change. Consumers who extend `PriceValue` (rare — it's a final-shaped data object) get the new methods for free; consumers who *use* `PriceValue` see new methods they can opt into.
- **Behavioural change for merchants**: only one — currency mismatches now throw at the point of the bad arithmetic instead of silently producing a wrong total. This will surface latent bugs in custom drivers/pipelines that previously combined mismatched currencies. Worth a CHANGELOG callout (`Cart pipelines now throw MismatchedCurrencyException on currency mix-ups that previously corrupted totals silently. If you see this in production, audit your custom tax/payment/shipping drivers.`).
- **Upgrade path for v1.x consumers**: no Rector rule needed — v1's `Price` data type had a different shape. v1→v2 migration goes through the Upgrade package's existing rewrites.
- **Translation / locale impact**: the `MismatchedCurrencyException` message is developer-facing, not user-facing. English-only, no locale work.
- **Filament / admin impact**: none — admin reads `PriceValue::format()` / `decimal()`, which this spec doesn't touch.

## Open questions

- **`PriceValue::sum()` on an empty iterable.** Spec proposes `new PriceValue(0, $currency)` with currency passed by caller. Alternative: throw on empty. Decision: zero-with-currency is the more useful default (cart with no lines should produce a zero subtotal, not blow up); the explicit-currency parameter makes "I forgot the currency" impossible. Owner: implementer.
- **`clampToZero()` naming.** Considered `max(0)` / `floor()` / `nonNegative()`. `clampToZero` is most discoverable for the intended use case (over-discount safety). Pin in PR.
- **Should `multiply()` accept a `float` multiplier too?** No — float multiplication is the calculator's `percentage()`, which is rounding-aware. `multiply()` is integer-only by design (line quantity, repeat count). Adding `float` would re-introduce the silent rounding problem 0014 just solved.
- **Should there be a `divide()` for proportional splits?** No — that's exactly what `PriceCalculatorInterface::distribute()` is for, and it handles the rounding remainder correctly. `divide(int $parts): array` on `PriceValue` would just delegate to `distribute`, adding a second name for the same op.
- **Benchmark target.** What's the acceptable allocation overhead for `Cart::calculate()` on a 50-line cart? Suggest: ≤5% wall-clock increase, measured against `2.x` head. If higher, fall back to inlined-sum optimisation (see section C). Owner: implementer.

## Sequencing

This spec lands **after** [[0014-price-calculator]]. 0014 establishes the calculator boundary; this spec defines the complementary value-object boundary. The two are designed in concert — landing them in order keeps each PR's diff focused on a single shape (rounding ops vs. currency-safe combination).

## References

- Spec [[0012-price-data-type-refactor]] — laid down the `PriceValue` boundary as a `readonly` integer cast; this spec adds methods to that object without re-introducing the per-attribute allocation cost.
- Spec [[0014-price-calculator]] — established `PriceCalculatorInterface` for rounding-sensitive ops; section C of that spec explicitly defers `add(PriceValue, PriceValue)` to this one.
- Inline arithmetic sites enumerated above; see `grep -rn '\->value\s*[+-]\s*\$.*\->value' packages/` and `grep -rn 'new PriceValue' packages/` for the full audit list.
