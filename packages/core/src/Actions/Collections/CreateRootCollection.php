<?php

namespace Lunar\Core\Actions\Collections;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\Contracts\Actions\Collections\CreatesRootCollection;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;

/**
 * Create a root-level collection under a collection group. Builds the
 * attribute_data bag from the configured `name` attribute type
 * (TranslatedText vs plain text) so consumers do not need to inspect the
 * Attribute model themselves.
 */
final class CreateRootCollection extends AbstractAction implements CreatesRootCollection
{
    public function execute(int $collectionGroupId, string|array $name): Collection
    {
        return DB::transaction(function () use ($collectionGroupId, $name): Collection {
            $type = $this->resolveNameType();

            /** @var Collection $collection */
            $collection = Collection::create([
                'collection_group_id' => $collectionGroupId,
                'attribute_data' => [
                    'name' => new $type($name),
                ],
            ]);

            return $collection;
        });
    }

    protected function resolveNameType(): string
    {
        return (string) Attribute::whereHandle('name')
            ->whereAttributeType(Collection::morphName())
            ->firstOrFail()
            ->type;
    }
}
