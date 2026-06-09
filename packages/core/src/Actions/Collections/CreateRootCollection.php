<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Contracts\Actions\Collections\CreatesRootCollection;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Language;

/**
 * Create a root-level collection under a collection group. The collection
 * `name` is a dedicated translatable column (spec 0018); a plain string is
 * stored under the default locale.
 */
class CreateRootCollection implements CreatesRootCollection
{
    public function execute(int $collectionGroupId, string|array $name): Collection
    {
        return DB::transaction(function () use ($collectionGroupId, $name): Collection {
            /** @var Collection $collection */
            $collection = Collection::create([
                'collection_group_id' => $collectionGroupId,
                'name' => $this->localiseName($name),
            ]);

            return $collection;
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
