<?php

namespace Lunar\Panel\Contracts;

use Lunar\Core\Models\Currency;

/**
 * How a discount type is edited in the panel.
 *
 * The renderer-agnostic counterpart of Filament's DiscountFormType: a type
 * describes its form here without depending on the panel's Vue layer or on
 * Filament, so one class can serve either admin. Implementations are separate
 * classes mapped by the owning Section through discountTypeForms(), which is
 * what keeps core's discount types free of any panel dependency.
 */
interface DiscountTypeForm
{
    /** Vue component id, resolved through the panel's component registry. */
    public function component(): string;

    /**
     * The target buckets this type reads, driving which "Applies to" blocks
     * render. A cart-level type that never touches lines returns none.
     *
     * @return array<int, string>
     */
    public function targetBuckets(): array;

    /**
     * Decode stored `data` for editing — minor units to decimals, and so on.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function toForm(array $data): array;

    /**
     * Encode the edited payload back to stored `data`.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function toStorage(array $data): array;

    /**
     * Validation rules for the type's `data.*` payload, keyed without the
     * `data.` prefix — the caller applies it.
     *
     * @return array<string, mixed>
     */
    public function rules(): array;

    /**
     * A one-line description of the effect, for the discounts list — "15% off",
     * "Buy 2, get 1". Null when the type cannot summarise itself from `data`
     * alone, which is what the list falls back on.
     *
     * @param  array<string, mixed>  $data  the stored payload, not the form view
     * @param  ?Currency  $currency  the store's default currency, for money amounts
     */
    public function summary(array $data, ?Currency $currency): ?string;
}
