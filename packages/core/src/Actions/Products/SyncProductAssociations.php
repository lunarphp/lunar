<?php

namespace Lunar\Core\Actions\Products;

use Lunar\Core\Contracts\Actions\Products\SyncsProductAssociations;
use Lunar\Core\Enums\Concerns\ProvidesProductAssociationType;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductAssociation;

/**
 * Replace a product's associations of one type with an ordered list of
 * target products. Targets already linked keep their row and take their new
 * sort, new targets get a row, and rows for targets no longer listed are
 * deleted. Duplicate ids, the product's own id and ids with no product are
 * dropped, so the result never holds two rows for one target and type.
 */
class SyncProductAssociations implements SyncsProductAssociations
{
    public function execute(Product $product, ProvidesProductAssociationType|string $type, array $targetIds): Product
    {
        $type = is_string($type) ? $type : $type->value;

        return DB::transaction(function () use ($product, $type, $targetIds): Product {
            $candidates = array_values(array_unique(array_map('intval', $targetIds)));
            $candidates = array_values(array_filter($candidates, fn (int $id): bool => $id !== $product->id));

            $known = Product::query()->whereIn('id', $candidates)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $targets = array_values(array_filter($candidates, fn (int $id): bool => in_array($id, $known, true)));

            $existing = $product->associations()->where('type', $type)->get();

            // One row per listed target is kept; rows for unlisted targets and
            // duplicate rows a queued associate() left behind are deleted.
            $rows = $existing
                ->filter(fn (ProductAssociation $association): bool => in_array((int) $association->product_target_id, $targets, true))
                ->unique('product_target_id')
                ->keyBy(fn (ProductAssociation $association): int => (int) $association->product_target_id);

            // Delete per model so the deleted event fires and cache
            // invalidation reaches both products; a bulk delete would skip it.
            $existing
                ->reject(fn (ProductAssociation $association): bool => $rows->contains($association))
                ->each->delete();

            foreach ($targets as $index => $targetId) {
                $sort = $index + 1;
                $row = $rows->get($targetId);

                if ($row === null) {
                    $product->associations()->create([
                        'product_target_id' => $targetId,
                        'type' => $type,
                        'sort' => $sort,
                    ]);

                    continue;
                }

                if ((int) $row->sort !== $sort) {
                    $row->update(['sort' => $sort]);
                }
            }

            return $product;
        });
    }
}
