<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Contracts\Collection as CollectionContract;
use Lunar\Core\Models\Language;

/**
 * Create a child collection under a parent, taking care of nested-set
 * `appendNode` linkage so descendants are positioned correctly. The
 * collection `name` is a dedicated translatable column (spec 0018); a plain
 * string is stored under the default locale.
 */
class CreateChildCollection implements CreatesChildCollection
{
    public function execute(CollectionContract $parent, string|array $name): Collection
    {
        return DB::transaction(function () use ($parent, $name): Collection {
            /** @var Collection $child */
            $child = Collection::create([
                'collection_group_id' => $parent->collection_group_id,
                'name' => $this->localiseName($name),
            ]);

            $parent->appendNode($child);

            return $child;
        });
    }

    /**
     * @param  string|array<string, string>  $name
     * @return array<string, string>
     */
    protected function localiseName(string|array $name): array
    {
        if (is_array($name)) {
            return $name;
        }

        return [Language::getDefault()?->code ?? config('app.locale') => $name];
    }
}
