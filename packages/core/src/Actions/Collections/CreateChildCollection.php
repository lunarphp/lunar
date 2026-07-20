<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Contracts\Actions\Collections\CreatesChildCollection;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Language;

/**
 * Create a child collection under a parent, taking care of nested-set
 * `appendNode` linkage so descendants are positioned correctly. The
 * collection `name` is a dedicated translatable column (spec 0018); a plain
 * string is stored under the default locale. Further column values (handle,
 * status, translated descriptions) ride in via `$attributes`; a missing
 * handle is generated from the name by the model's creating hook.
 */
class CreateChildCollection implements CreatesChildCollection
{
    public function execute(Collection $parent, string|array $name, array $attributes = []): Collection
    {
        return DB::transaction(function () use ($parent, $name, $attributes): Collection {
            /** @var Collection $child */
            $child = Collection::create([
                ...$attributes,
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
