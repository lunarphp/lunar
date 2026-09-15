<?php

namespace Lunar\Bundles\Actions;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Collection;
use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Contracts\Actions\SyncsBundleComponents;
use Lunar\Bundles\Exceptions\BundleNesting;
use Lunar\Bundles\Exceptions\InvalidBundleDefinition;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Core\Models\ProductVariant;

class SyncBundleComponents implements SyncsBundleComponents
{
    public function __construct(
        protected RepricesBundle $reprice,
        protected Config $config,
    ) {}

    public function execute(Bundle $bundle, array $components): Bundle
    {
        $rows = $this->normalise($components);

        $this->guard($bundle, $rows);

        $bundle->getConnection()->transaction(function () use ($bundle, $rows) {
            $existing = $bundle->components()->get();
            $kept = [];

            foreach ($rows as $row) {
                /** @var BundleComponent $component */
                $component = $existing->first(
                    fn (BundleComponent $component) => $component->bundle_group_id === $row['bundle_group_id']
                        && $component->product_variant_id === $row['product_variant_id']
                ) ?? $bundle->components()->make();

                $component->fill($row)->save();

                $kept[] = $component->getKey();
            }

            $existing->whereNotIn('id', $kept)->each(fn (BundleComponent $component) => $component->delete());
        });

        $bundle->unsetRelation('components');
        $bundle->unsetRelation('fixedComponents');

        return $this->reprice->execute($bundle);
    }

    /**
     * Collapse the input to plain column values, the last entry for a
     * (group, variant) pair winning.
     *
     * @param  list<array{variant: ProductVariant|int, quantity: int, group?: BundleGroup|int|null, default?: bool, position?: int}>  $components
     * @return Collection<string, array{bundle_group_id: ?int, product_variant_id: int, quantity: int, default: bool, position: int}>
     */
    protected function normalise(array $components): Collection
    {
        $rows = collect();

        foreach (array_values($components) as $index => $component) {
            $variantId = $component['variant'] instanceof ProductVariant
                ? (int) $component['variant']->getKey()
                : (int) $component['variant'];

            $group = $component['group'] ?? null;
            $groupId = $group instanceof BundleGroup ? (int) $group->getKey() : ($group === null ? null : (int) $group);

            $rows["{$groupId}:{$variantId}"] = [
                'bundle_group_id' => $groupId,
                'product_variant_id' => $variantId,
                'quantity' => max(1, (int) ($component['quantity'] ?? 1)),
                'default' => (bool) ($component['default'] ?? false),
                'position' => (int) ($component['position'] ?? $index),
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<string, array{bundle_group_id: ?int, product_variant_id: int, quantity: int, default: bool, position: int}>  $rows
     */
    protected function guard(Bundle $bundle, Collection $rows): void
    {
        if ($rows->isEmpty()) {
            throw InvalidBundleDefinition::empty();
        }

        $max = (int) $this->config->get('lunar.bundles.max_components', 25);

        if ($rows->count() > $max) {
            throw InvalidBundleDefinition::tooManyComponents($max);
        }

        $variantIds = $rows->pluck('product_variant_id')->unique();

        if ($variantIds->contains((int) $bundle->product_variant_id)) {
            throw BundleNesting::self();
        }

        if (Bundle::query()->whereIn('product_variant_id', $variantIds)->exists()) {
            throw BundleNesting::isBundle();
        }

        $groups = $bundle->loadMissing('groups')->groups;
        $groupIds = $rows->pluck('bundle_group_id')->filter()->unique();

        if ($groupIds->diff($groups->pluck('id'))->isNotEmpty()) {
            throw InvalidBundleDefinition::unknownGroup();
        }

        foreach ($groups as $group) {
            $count = $rows->where('bundle_group_id', (int) $group->getKey())->count();

            if ($count > 0 && $group->max_selections > $count) {
                throw InvalidBundleDefinition::groupSelections((string) $group->translate('name'));
            }
        }
    }
}
