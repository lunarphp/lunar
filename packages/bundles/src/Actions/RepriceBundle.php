<?php

namespace Lunar\Bundles\Actions;

use Illuminate\Support\Collection;
use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Enums\BundlePricing;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Pricing\PriceCalculatorInterface;

/**
 * Materialise the derived price of a `components` bundle as real `prices`
 * rows on the bundle variant (spec 0085, section 2.5), from the default
 * selection: fixed components plus each group's default components.
 *
 * Ownership: in `components` mode the package owns every `min_quantity = 1`
 * row on the bundle variant. Each reprice rewrites the rows that still apply
 * and deletes the rest, so a currency a component no longer has a price in
 * loses its row. Quantity price breaks (`min_quantity > 1`) are never touched.
 */
class RepriceBundle implements RepricesBundle
{
    public function __construct(
        protected PriceCalculatorInterface $calculator,
    ) {}

    public function execute(Bundle $bundle): Bundle
    {
        if ($bundle->pricing !== BundlePricing::Components) {
            return $bundle;
        }

        $bundle->loadMissing(['variant', 'components.variant.prices']);

        $components = $bundle->components
            ->filter(fn (BundleComponent $component) => $component->isFixed() || $component->default)
            ->values();

        // Nothing to derive from yet; leave whatever rows the variant has alone.
        if ($components->isEmpty() || ! $bundle->variant) {
            return $bundle;
        }

        $variant = $bundle->variant;
        $customerGroups = CustomerGroup::query()->get();
        $written = [];

        foreach (Currency::query()->get() as $currency) {
            $sum = $this->sum($components, $currency, null);

            if ($sum === null) {
                continue;
            }

            $written[] = $this->write($bundle, $variant, $currency, null, $sum);

            foreach ($customerGroups as $customerGroup) {
                if (! $this->anyGroupPrice($components, $currency, $customerGroup)) {
                    continue;
                }

                $written[] = $this->write($bundle, $variant, $currency, $customerGroup, (int) $this->sum($components, $currency, $customerGroup));
            }
        }

        Price::query()
            ->where('priceable_type', $variant->getMorphClass())
            ->where('priceable_id', $variant->getKey())
            ->where('min_quantity', 1)
            ->whereNotIn('id', $written)
            ->get()
            ->each(fn (Price $price) => $price->delete());

        $variant->unsetRelation('prices');

        return $bundle;
    }

    /**
     * Quantity-weighted sum of the components, each at its customer-group
     * price when one exists, otherwise its base price. Null when a component
     * has no base price in the currency.
     *
     * @param  Collection<int, BundleComponent>  $components
     */
    protected function sum(Collection $components, Currency $currency, ?CustomerGroup $customerGroup): ?int
    {
        $sum = 0;

        foreach ($components as $component) {
            $price = ($customerGroup ? $this->priceRow($component->variant, $currency, $customerGroup) : null)
                ?? $this->priceRow($component->variant, $currency, null);

            if (! $price) {
                return null;
            }

            $sum += (int) $price->price * $component->quantity;
        }

        return $sum;
    }

    /**
     * @param  Collection<int, BundleComponent>  $components
     */
    protected function anyGroupPrice(Collection $components, Currency $currency, CustomerGroup $customerGroup): bool
    {
        return $components->contains(
            fn (BundleComponent $component) => $this->priceRow($component->variant, $currency, $customerGroup) !== null
        );
    }

    protected function priceRow(ProductVariant $variant, Currency $currency, ?CustomerGroup $customerGroup): ?Price
    {
        return $variant->prices->first(
            fn (Price $price) => (int) $price->currency_id === (int) $currency->getKey()
                && (int) $price->min_quantity === 1
                && $price->customer_group_id === $customerGroup?->getKey()
        );
    }

    protected function write(Bundle $bundle, ProductVariant $variant, Currency $currency, ?CustomerGroup $customerGroup, int $sum): int
    {
        $discount = $bundle->discount_percentage
            ? $this->calculator->percentage($sum, $bundle->discount_percentage / 100, $currency)
            : 0;

        $price = Price::query()->firstOrNew([
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->getKey(),
            'currency_id' => $currency->getKey(),
            'customer_group_id' => $customerGroup?->getKey(),
            'min_quantity' => 1,
        ]);

        $price->fill([
            'price' => $sum - $discount,
            'list_price' => $discount > 0 ? $sum : null,
        ])->save();

        return (int) $price->getKey();
    }
}
