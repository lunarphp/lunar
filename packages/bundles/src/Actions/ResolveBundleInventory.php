<?php

namespace Lunar\Bundles\Actions;

use Illuminate\Support\Collection;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Core\Contracts\Actions\Products\ResolvesInventory;
use Lunar\Core\DataObjects\VariantInventory;
use Lunar\Core\Models\ProductVariant;

/**
 * Decorates the core inventory seam: a bundle variant's availability is how
 * many complete bundles its components can supply; every other variant falls
 * through to the inner action (spec 0085, section 2.7).
 */
class ResolveBundleInventory implements ResolvesInventory
{
    public function __construct(
        protected ResolvesInventory $inner,
    ) {}

    public function execute(ProductVariant $variant): VariantInventory
    {
        /** @var ?Bundle $bundle */
        $bundle = $variant->loadMissing('bundle')->bundle;

        if (! $bundle) {
            return $this->inner->execute($variant);
        }

        $bundle->loadMissing(['groups', 'components.variant']);

        // Answered through the inner action, never the component variant's own
        // methods, which would re-enter this decorator.
        $inventories = $bundle->components->mapWithKeys(fn (BundleComponent $component) => [
            $component->getKey() => $this->inner->execute($component->variant),
        ]);

        $candidates = $this->candidates($bundle, $inventories);

        if ($candidates->isEmpty()) {
            return new VariantInventory(available: 0, unlimited: false);
        }

        return new VariantInventory(
            available: (int) $candidates->min(fn (BundleComponent $component) => $this->completeUnits($component, $inventories)),
            unlimited: $candidates->every(fn (BundleComponent $component) => $inventories[$component->getKey()]->unlimited),
        );
    }

    /**
     * Fixed components plus, per group, the best-stocked option: "available"
     * means at least one valid configuration can be sold.
     *
     * @param  Collection<int, VariantInventory>  $inventories
     * @return Collection<int, BundleComponent>
     */
    protected function candidates(Bundle $bundle, Collection $inventories): Collection
    {
        $candidates = $bundle->components->filter(fn (BundleComponent $component) => $component->isFixed());

        foreach ($bundle->groups as $group) {
            $best = $bundle->components
                ->where('bundle_group_id', $group->getKey())
                ->sortByDesc(fn (BundleComponent $component) => $inventories[$component->getKey()]->unlimited
                    ? PHP_INT_MAX
                    : $this->completeUnits($component, $inventories))
                ->first();

            // A required group with nothing to choose from cannot be sold at all.
            if (! $best && $group->min_selections > 0) {
                return collect();
            }

            if ($best) {
                $candidates->push($best);
            }
        }

        return $candidates->values();
    }

    /**
     * @param  Collection<int, VariantInventory>  $inventories
     */
    protected function completeUnits(BundleComponent $component, Collection $inventories): int
    {
        return intdiv(max(0, $inventories[$component->getKey()]->available), max(1, $component->quantity));
    }
}
