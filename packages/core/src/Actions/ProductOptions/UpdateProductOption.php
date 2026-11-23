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

            $value->colour = $isColour && ! empty($row['colour'])
                ? $this->normaliseColour($row['colour'])
                : null;

            $value->save();
        }
    }

    /**
     * Validate a hex colour (`#` and three or six hex digits) and return its
     * six-digit upper-case form, so `#abc` is stored as `#AABBCC`.
     */
    protected function normaliseColour(string $colour): string
    {
        if (! preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $colour, $matches)) {
            throw new ProductOptionActionException(
                __('lunar::exceptions.product_option_invalid_colour', ['colour' => $colour])
            );
        }

        $digits = $matches[1];

        if (strlen($digits) === 3) {
            $digits = $digits[0].$digits[0].$digits[1].$digits[1].$digits[2].$digits[2];
        }

        return '#'.strtoupper($digits);
    }

    /** Clear the colour and swatch media of every value after a type change. */
    protected function clearValuePayloads(ProductOption $productOption): void
    {
        $productOption->values()->get()->each(function (ProductOptionValue $value): void {
            $value->update(['colour' => null]);

            $value->clearMediaCollection(config('lunar.media.collection'));
        });
    }
}
