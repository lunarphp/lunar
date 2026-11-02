<?php

namespace Lunar\Bundles\Actions;

use Illuminate\Support\Collection;
use Lunar\Bundles\Contracts\Actions\ResolvesBundleSelection;
use Lunar\Bundles\Exceptions\InvalidBundleSelection;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;
use Lunar\Bundles\ValueObjects\BundleSelection;
use Lunar\Bundles\ValueObjects\SelectedComponent;

/**
 * The single place cart line meta is interpreted (spec 0085, section 2.6).
 *
 * `meta.bundle.selections` maps a group's public id to the public ids of the
 * components chosen from it. Fixed components are always included. A group
 * absent from meta contributes nothing when optional, otherwise its default
 * components when they satisfy the minimum.
 */
class ResolveBundleSelection implements ResolvesBundleSelection
{
    public function execute(Bundle $bundle, array $meta = []): BundleSelection
    {
        $bundle->loadMissing(['groups', 'components.variant']);

        $selections = $this->selectionsFrom($meta);
        $groupIds = $bundle->groups->pluck('public_id');

        foreach (array_keys($selections) as $publicId) {
            if (! $groupIds->contains($publicId)) {
                throw InvalidBundleSelection::unknownGroup((string) $publicId);
            }
        }

        /** @var Collection<int, BundleComponent> $chosen */
        $chosen = $bundle->components->where('bundle_group_id', null)->values();

        foreach ($bundle->groups as $group) {
            $chosen = $chosen->merge($this->resolveGroup($bundle, $group, $selections[$group->public_id] ?? null));
        }

        $defaultIds = $bundle->components
            ->filter(fn (BundleComponent $component) => $component->isFixed() || $component->default)
            ->pluck('id')->sort()->values();

        $components = $chosen->map(fn (BundleComponent $component) => new SelectedComponent(
            variant: $component->variant,
            quantity: $component->quantity,
            group: $component->bundle_group_id ? $bundle->groups->firstWhere('id', $component->bundle_group_id) : null,
            component: $component,
        ));

        return new BundleSelection(
            components: $components->values(),
            default: $chosen->pluck('id')->sort()->values()->all() === $defaultIds->all(),
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, list<string>>
     */
    protected function selectionsFrom(array $meta): array
    {
        $selections = $meta['bundle']['selections'] ?? [];

        if (! is_array($selections)) {
            throw InvalidBundleSelection::malformed();
        }

        foreach ($selections as $ids) {
            if (! is_array($ids)) {
                throw InvalidBundleSelection::malformed();
            }
        }

        return $selections;
    }

    /**
     * @param  ?list<string>  $publicIds
     * @return Collection<int, BundleComponent>
     */
    protected function resolveGroup(Bundle $bundle, BundleGroup $group, ?array $publicIds): Collection
    {
        $name = (string) $group->translate('name');
        $options = $bundle->components->where('bundle_group_id', $group->getKey())->values();

        if ($publicIds === null) {
            if ($group->min_selections === 0) {
                return collect();
            }

            $defaults = $options->where('default', true)->values();

            if ($defaults->count() < $group->min_selections || $defaults->count() > $group->max_selections) {
                throw InvalidBundleSelection::count($name, $group->min_selections, $group->max_selections);
            }

            return $defaults;
        }

        $publicIds = array_map('strval', $publicIds);

        if (count($publicIds) !== count(array_unique($publicIds))) {
            throw InvalidBundleSelection::duplicateComponent($name);
        }

        $chosen = collect($publicIds)->map(function (string $publicId) use ($options, $name) {
            return $options->firstWhere('public_id', $publicId)
                ?? throw InvalidBundleSelection::unknownComponent($publicId, $name);
        });

        if ($chosen->count() < $group->min_selections || $chosen->count() > $group->max_selections) {
            throw InvalidBundleSelection::count($name, $group->min_selections, $group->max_selections);
        }

        return $chosen;
    }
}
