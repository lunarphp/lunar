<?php

namespace Lunar\Bundles\Actions;

use Lunar\Bundles\Contracts\Actions\RepricesBundle;
use Lunar\Bundles\Contracts\Actions\SyncsBundleGroups;
use Lunar\Bundles\Exceptions\InvalidBundleDefinition;
use Lunar\Bundles\Models\Bundle;
use Lunar\Bundles\Models\BundleComponent;
use Lunar\Bundles\Models\BundleGroup;

class SyncBundleGroups implements SyncsBundleGroups
{
    public function __construct(
        protected RepricesBundle $reprice,
    ) {}

    public function execute(Bundle $bundle, array $groups): Bundle
    {
        $bundle->getConnection()->transaction(function () use ($bundle, $groups) {
            $existing = $bundle->groups()->get();
            $kept = [];

            foreach (array_values($groups) as $index => $data) {
                $name = $this->nameOf($data['name']);
                $min = (int) $data['min_selections'];
                $max = (int) $data['max_selections'];

                if ($min < 0 || $min > $max) {
                    throw InvalidBundleDefinition::groupSelections($name);
                }

                /** @var ?BundleGroup $group */
                $group = isset($data['id']) ? $existing->firstWhere('id', (int) $data['id']) : null;

                if (isset($data['id']) && ! $group) {
                    throw InvalidBundleDefinition::unknownGroup();
                }

                $group ??= $bundle->groups()->make();

                if ($group->exists) {
                    $count = $group->components()->count();

                    if ($count > 0 && $max > $count) {
                        throw InvalidBundleDefinition::groupSelections($name);
                    }
                }

                $group->fill([
                    'name' => $data['name'],
                    'min_selections' => $min,
                    'max_selections' => $max,
                    'position' => (int) ($data['position'] ?? $index),
                ])->save();

                $kept[] = $group->getKey();
            }

            $existing->whereNotIn('id', $kept)->each(function (BundleGroup $group) {
                $group->components()->get()->each(fn (BundleComponent $component) => $component->delete());
                $group->delete();
            });
        });

        $bundle->unsetRelation('groups');
        $bundle->unsetRelation('components');
        $bundle->unsetRelation('fixedComponents');

        return $this->reprice->execute($bundle);
    }

    /**
     * @param  array<string, string>|string  $name
     */
    protected function nameOf(array|string $name): string
    {
        if (is_string($name)) {
            return $name;
        }

        return (string) ($name[app()->getLocale()] ?? reset($name) ?: '');
    }
}
