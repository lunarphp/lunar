# 0012 — Price data type / cast refactor

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-05-26
- TODO item: "Price data type performance changes"

## Problem

Every money column on `Order`, `OrderLine`, `Cart`, `CartLine`, `Transaction`, and `Price` is cast through `Lunar\Core\Base\Casts\Price` into a `Lunar\Core\DataTypes\Price` value object. The cast looks cheap; in practice it is not.

- `Lunar\Core\Base\Casts\Price::get()` reads `$model->currency` on every access (a relation lookup, falling back to `Currency::getDefault()` — another query when the default isn't cached). `OrderLine`, `Transaction`, and `Price` have a column with a *line* of cast invocations; rendering an order summary multiplies that out across every line and column.
- The cast then walks `$model->priceable` to grab `unit_quantity`, which adds a relation check (`relationLoaded('priceable')`) per call. Only `Lunar\Core\Models\Price` actually has that relation; on the other models the lookup is dead weight every read. (Spec 0011 hardened the cast against strict mode but did not eliminate the cost.)
- Each access boxes the integer into a new `PriceDataType` instance whose only job is to proxy four methods (`decimal()`, `unitDecimal()`, `formatted()`, `unitFormatted()`) to a freshly-resolved `Lunar\Core\Pricing\DefaultPriceFormatter` via `app(...)`. The container resolution happens inside `Lunar\Core\DataTypes\Price::formatter()` *per call*, not per object — so `$order->total->formatted()` does a container hit, and `$order->total->decimal()` does another one immediately after.
- Validating each integer column via `Validator::make(...)->validate()` inside the cast (`Lunar\Core\Base\Casts\Price::get()` line 33) is more or less free per call but pure waste at scale.
- Pipelines that need a `Currency`-aware integer (`Lunar\Core\Pipelines\Cart\CalculateLines`, `Lunar\Core\Drivers\SystemTaxDriver`, `Lunar\Core\DiscountTypes\AmountOff`, etc.) construct `new Price(int, Currency)` purely to pass it across an action boundary — never to read a formatted value. The object is being used as a tuple.

Net effect: a cart with 10 lines × 6 money columns each is allocating 60 `PriceDataType` objects, running 60 currency lookups, and 60 validator runs *just to put numbers on the model*. Formatting them later adds container resolutions on top. None of that work is necessary — the underlying value is always an integer minor-unit, and the currency lives one relation away on the parent.

The accessor ergonomics (`$order->total->formatted()`, `$line->unit_price->decimal()`) are nice, but they are bought with a per-attribute object allocation. We can keep the ergonomics on the *model* and drop the per-attribute object entirely.

## Proposal

Three pieces, in this order: introduce the replacement; migrate models off the cast; delete the cast and data type.

### A. Replacement infrastructure

Add the following, leaving the existing cast/data type in place so the migration can land model-by-model.

1. **`Lunar\Core\Contracts\HasCurrency`** — single-method interface for anything that owns a price-like value.

   ```php
   namespace Lunar\Core\Contracts;

   use Lunar\Core\Models\Contracts\Currency;

   interface HasCurrency
   {
       public function resolveCurrency(): Currency;
   }
   ```

2. **`Lunar\Core\Models\Concerns\FormatsPrices`** — trait that replaces the cast for Eloquent models. Currency resolution is memoised on the instance.

   ```php
   namespace Lunar\Core\Models\Concerns;

   use Illuminate\Database\Eloquent\Model;
   use Lunar\Core\Contracts\HasCurrency;
   use Lunar\Core\Models\Contracts\Currency;
   use Lunar\Core\Pricing\PriceFormatterInterface;

   /**
    * @mixin Model
    * @see HasCurrency
    */
   trait FormatsPrices
   {
       private ?Currency $resolvedCurrency = null;

       public function format(string $field, ?string $locale = null, ?int $decimalPlaces = null, bool $trimTrailingZeros = true): ?string
       {
           $value = $this->getAttribute($field);
           if ($value === null) { return null; }

           return app(PriceFormatterInterface::class, [
               'value' => (int) $value,
               'currency' => $this->getCachedCurrency(),
           ])->formatted($locale, decimalPlaces: $decimalPlaces, trimTrailingZeros: $trimTrailingZeros);
       }

       public function decimal(string $field, bool $rounding = true): ?float
       {
           $value = $this->getAttribute($field);
           if ($value === null) { return null; }

           return app(PriceFormatterInterface::class, [
               'value' => (int) $value,
               'currency' => $this->getCachedCurrency(),
           ])->decimal($rounding);
       }

       private function getCachedCurrency(): Currency
       {
           return $this->resolvedCurrency ??= $this->resolveCurrency();
       }
   }
   ```

3. **`Lunar\Core\DataObjects\PriceValue`** — for non-Eloquent price values (computed tax-aware prices, pipeline payloads). Implements `HasCurrency`, reuses the trait with renamed methods so callers don't pass a column name.

   ```php
   namespace Lunar\Core\DataObjects;

   use Lunar\Core\Contracts\HasCurrency;
   use Lunar\Core\Models\Concerns\FormatsPrices;
   use Lunar\Core\Models\Contracts\Currency;

   class PriceValue implements HasCurrency
   {
       use FormatsPrices {
           format as private traitFormat;
           decimal as private traitDecimal;
       }

       public function __construct(
           public readonly int $value,
           private readonly Currency $currency,
       ) {}

       public function getAttribute(string $key): mixed
       {
           return $this->{$key};
       }

       public function resolveCurrency(): Currency
       {
           return $this->currency;
       }

       public function format(?string $locale = null, ?int $decimalPlaces = null, bool $trimTrailingZeros = true): ?string
       {
           return $this->traitFormat('value', $locale, $decimalPlaces, $trimTrailingZeros);
       }

       public function decimal(bool $rounding = true): ?float
       {
           return $this->traitDecimal('value', $rounding);
       }
   }
   ```

`Lunar\Core\Pricing\PriceFormatterInterface` and `Lunar\Core\Pricing\DefaultPriceFormatter` stay where they are. `unitQty` remains a formatter constructor parameter; nothing in the migrated models needs it, so the trait does not surface it. Callers that need unit-level formatting resolve `PriceFormatterInterface` directly with the `unitQty` they want.

### B. Migrate models off the cast

For each model that currently casts to `Lunar\Core\Base\Casts\Price`:

1. Add `implements HasCurrency` and `use FormatsPrices`.
2. Implement `resolveCurrency(): Currency` — usually `$this->loadMissing('currency'); return $this->currency;`, or hop via parent (`cart`, `order`).
3. Replace each `'col' => Price::class` cast with `'col' => 'integer'`.
4. Update internal call sites that read `->decimal()` / `->formatted()` off the old value object.

Models in scope (file paths):

- `packages/core/src/Models/Price.php` — `price`, `compare_price` (currently `CastsPrice::class`).
- `packages/core/src/Models/OrderLine.php` — `unit_price`, `sub_total`, `tax_total`, `discount_total`, `total`.
- `packages/core/src/Models/Order.php` — `sub_total`, `discount_total`, `tax_total`, `total`, `shipping_total`.
- `packages/core/src/Models/Transaction.php` — `amount`.
- `packages/core/src/Models/Cart.php` and `CartLine.php` — every money column the cart pipeline writes (`unit_price`, `sub_total`, `discount_total`, `tax_amount`, `total`, etc.).

Pipeline / action call sites that today construct `new Price(...)` purely to pass an integer + currency tuple (`Lunar\Core\Pipelines\Cart\CalculateLines`, `Lunar\Core\Pipelines\Cart\CalculateTax`, `Lunar\Core\Pipelines\Cart\CalculateShippingSubTotal`, `Lunar\Core\Pipelines\Cart\ApplyShipping`, `Lunar\Core\Pipelines\CartLine\GetUnitPrice`, `Lunar\Core\Actions\Carts\CalculateLine*`, `Lunar\Core\DiscountTypes\AmountOff`, `Lunar\Core\DiscountTypes\BuyXGetY`, `Lunar\Core\Drivers\SystemTaxDriver`) switch to plain `int` where the boundary doesn't need formatting, or to `PriceValue` where it does.

Casts in `Lunar\Core\Base\Casts\TaxBreakdown` and `Lunar\Core\Base\Casts\DiscountBreakdown` that hydrate child `Price` objects switch to constructing `PriceValue` (or plain ints — they're inside a DTO already).

### C. Call-site shape

| Before (v1)                          | After (v2)                                            |
| ------------------------------------ | ----------------------------------------------------- |
| `$order->total->value`               | `$order->total` (raw `int`)                           |
| `$order->total->decimal()`           | `$order->decimal('total')`                            |
| `$order->total->formatted()`         | `$order->format('total')`                             |
| `$order->total->formatted('fr_FR')`  | `$order->format('total', 'fr_FR')`                    |
| `$line->unit_price->unitDecimal()`   | resolve `PriceFormatterInterface` with `unitQty` directly |
| `$line->unit_price->unitFormatted()` | same                                                  |

### D. Delete the legacy

Final PR:

- Delete `Lunar\Core\Base\Casts\Price`.
- Delete `Lunar\Core\DataTypes\Price` (and its exception path in `Lunar\Core\Exceptions\InvalidDataTypeValueException` if nothing else uses it).
- Delete the matching tests (`tests/Unit/DataTypes/PriceTest.php`, `tests/Unit/Base/Casts/PriceTest.php` — names approximate; whatever exercises the deleted classes).
- Drop `@property \Lunar\Core\DataTypes\Price` PHPDoc on the migrated models and update `Lunar\Core\Models\Contracts\Price` (the contract method return types currently say `int|\Lunar\Core\DataTypes\Price`).

## Alternatives considered

- **Cache the currency inside the existing cast.** Would knock out the per-attribute currency lookup but not the per-attribute object allocation, container resolution per `formatted()`/`decimal()`, or the dead `priceable` walk on `OrderLine`/`Transaction`. Half the win for the same surface area.
- **Keep `DataTypes\Price` as a thin value object (int + Currency only), drop the formatter proxying.** Considered. Removes the container hit per format call but still allocates an object per attribute access. The trait approach gives equivalent ergonomics (`$order->format('total')` reads as well as `$order->total->formatted()`) without the allocation.
- **Make money columns plain `'integer'` with no helpers at all; force callers to use `PriceFormatterInterface` directly.** Rejected — the formatting ergonomics are the reason the cast exists in the first place. The trait keeps them.
- **Do nothing.** The cost is per attribute access on hot models (`Order`, `OrderLine`, `Cart`, `CartLine`), in code paths that already run on every cart recalculate. It compounds with line count.

## Migration impact

- **Database migrations**: none. Underlying columns stay integer; only the cast type changes.
- **Public contract surface**: breaking on the read side. Anything reading `$model->total->...` against a v2 Lunar model gets an `int` back. New surface added: `HasCurrency`, `FormatsPrices`, `PriceValue`, plus `format()` / `decimal()` methods on each migrated model. The contract `Lunar\Core\Models\Contracts\Price` methods (`priceExTax`, `priceIncTax`, `comparePriceIncTax`) change their return types from `int|\Lunar\Core\DataTypes\Price` to `int|PriceValue`.
- **Upgrade path for v1.x consumers**: Rector rules ship in the Upgrade package (per spec 0001):
  - `$model->col->value` → `$model->col`
  - `$model->col->decimal(...)` → `$model->decimal('col', ...)`
  - `$model->col->formatted(...)` → `$model->format('col', ...)`
  - `$model->col->unitDecimal(...)` / `unitFormatted(...)` → explicit `app(PriceFormatterInterface::class, [...])->unitDecimal(...)` rewrite. (May need to bail on dynamic property access; document as a manual step where Rector can't infer the column statically.)
- **Translation / locale impact**: none. No new translation keys.
- **Filament / admin impact**: any Filament column / infolist entry that called `->formatted()` or `->decimal()` on a money attribute needs swapping to `$record->format('col')` / `$record->decimal('col')`. The audit covers `lunarphp/admin` and `lunarphp/filament` resources for `Order`, `Cart`, `Transaction`, and `Price` (collection prices, variant prices). Resources published via `lunar:admin:publish` (spec 0010) pick up the call-site changes on re-publish; existing published copies keep working until they update — their Filament column will render the raw int once the model attribute stops being a `PriceDataType`, so the consumer sees the regression immediately and follows the upgrade guide.

## Open questions

- **`PriceValue` ↔ contract return types.** `Lunar\Core\Models\Contracts\Price::priceIncTax()` etc. today return `int|Lunar\Core\DataTypes\Price`. Switching to `int|PriceValue` is the obvious replacement, but worth asking whether the union is still useful or whether those methods should pick one (probably `PriceValue`, since callers want the formatter helpers). Owner: implementer; resolve before `accepted`.
- **`unitQty` callers.** Need a grep pass for `unitDecimal` / `unitFormatted` across `packages/` and the host app to confirm "none of the migrated models needed it" still holds and to document the manual upgrade case in the Upgrade package. Owner: implementer.
- **Benchmark.** Worth landing a micro-benchmark (cart recalculate with 10 lines × N attribute reads) before/after to put a number on the win. Not blocking the spec; useful for the PR description.

## References

- Existing cast: `packages/core/src/Base/Casts/Price.php`.
- Existing data type: `packages/core/src/DataTypes/Price.php`.
- Existing formatter (unchanged): `packages/core/src/Pricing/DefaultPriceFormatter.php`, `packages/core/src/Pricing/PriceFormatterInterface.php`.
- Related specs: [[0001-upgrade-package]] (Rector rules land here), [[0002-core-namespace]], [[0011-prevent-lazy-loading]] (cast was hardened for strict mode in 0011; this spec retires it).
