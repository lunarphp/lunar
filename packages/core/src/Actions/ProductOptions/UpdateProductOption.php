<?php

namespace Lunar\Core\Actions\ProductOptions;

use Lunar\Core\Contracts\Actions\ProductOptions\UpdatesProductOption;
use Lunar\Core\Enums\ProductOptionType;
use Lunar\Core\Exceptions\ProductOptionActionException;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\ProductOption;
use Lunar\Core\Models\ProductOptionValue;

/**
 * Update a product option and, when supplied, sync its values in one pass.
 *
 * The `values` key carries `{id?, name, position, colour?}` rows replacing the
 * option's value set; rows with an id update in place so variant links are
 * kept. A value still carried by a variant cannot be removed.
 *
 * Changing the option `type` clears every value's per-type payload (colour and
 * swatch image) while keeping names, positions, and variant links — swatch
 * uploads are managed separately through the media flow, not this action.
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

        $typeChanged = array_key_exists('type', $attributes)
            && $attributes['type'] !== $productOption->type;

        DB::transaction(function () use ($productOption, $attributes, $values, $typeChanged): void {
            $productOption->update($attributes);

            if ($typeChanged) {
                $this->clearValuePayloads($productOption);
            }

            if ($values !== null) {
                $this->syncValues($productOption, $values);
            }
        });

        return $productOption;
    }

    /**
     * @param  array<int, array{id?: int|null, name: array<string, string>, position?: int, colour?: ?string}>  $values
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

        $isColour = $productOption->type === ProductOptionType::Colour->value;

        foreach ($values as $index => $row) {
            /** @var ProductOptionValue $value */
            $value = isset($row['id'])
                ? $productOption->values()->findOrFail($row['id'])
                : $productOption->values()->make();

            $value->fill([
                'name' => $row['name'],
                'position' => $row['position'] ?? $index + 1,
            ]);

            $meta = (array) ($value->meta ?? []);

            if ($isColour && ! empty($row['colour'])) {
                $meta['colour'] = strtoupper($row['colour']);
            } else {
                unset($meta['colour']);
            }

            $value->meta = $meta ?: null;
            $value->save();
        }
    }

    /** Strip colour meta and swatch media from every value after a type change. */
    protected function clearValuePayloads(ProductOption $productOption): void
    {
        $productOption->values()->get()->each(function (ProductOptionValue $value): void {
            $meta = (array) ($value->meta ?? []);
            unset($meta['colour']);
            $value->meta = $meta ?: null;
            $value->save();

            $value->clearMediaCollection(config('lunar.media.collection'));
        });
    }
}
