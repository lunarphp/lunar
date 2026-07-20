<?php

namespace Lunar\Core\Actions\ProductOptions;

use Lunar\Core\Contracts\Actions\ProductOptions\UpdatesProductOption;
use Lunar\Core\Exceptions\ProductOptionActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;

/**
 * Update a product option and, when supplied, sync its values in one pass.
 *
 * The `values` key carries `{id?, name, position}` rows replacing the
 * option's value set; rows with an id update in place so variant links are
 * kept. A value still carried by a variant cannot be removed.
 */
class UpdateProductOption implements UpdatesProductOption
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ProductOption $productOption, array $attributes): ProductOption
    {
        $values = $attributes['values'] ?? null;
        unset($attributes['values']);

        DB::transaction(function () use ($productOption, $attributes, $values): void {
            $productOption->update($attributes);

            if ($values !== null) {
                $this->syncValues($productOption, $values);
            }
        });

        return $productOption;
    }

    /**
     * @param  array<int, array{id?: int|null, name: array<string, string>, position?: int}>  $values
     */
    protected function syncValues(ProductOption $productOption, array $values): void
    {
        $keepIds = collect($values)->pluck('id')->filter();

        $stale = $productOption->values()->whereNotIn('id', $keepIds)->get();

        $stale->each(function (ProductOptionValue $value): void {
            if ($value->variants()->exists()) {
                throw new ProductOptionActionException('Cannot remove an option value carried by product variants.');
            }

            $value->delete();
        });

        foreach ($values as $index => $row) {
            /** @var ProductOptionValue $value */
            $value = isset($row['id'])
                ? $productOption->values()->findOrFail($row['id'])
                : $productOption->values()->make();

            $value->fill([
                'name' => $row['name'],
                'position' => $row['position'] ?? $index + 1,
            ])->save();
        }
    }
}
