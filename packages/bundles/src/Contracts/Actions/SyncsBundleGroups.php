<?php

namespace Lunar\Bundles\Contracts\Actions;

use Lunar\Bundles\Exceptions\InvalidBundleDefinition;
use Lunar\Bundles\Models\Bundle;

interface SyncsBundleGroups
{
    /**
     * Upsert and delete groups so the bundle matches `$groups`, then reprice.
     * Deleting a group deletes its components.
     *
     * @param  list<array{id?: int, name: array<string, string>, min_selections: int, max_selections: int, position?: int}>  $groups
     *
     * @throws InvalidBundleDefinition
     */
    public function execute(Bundle $bundle, array $groups): Bundle;
}
