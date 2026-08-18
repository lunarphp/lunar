<?php

namespace Lunar\Core\Actions\Attributes;

use Lunar\Core\Contracts\Actions\Attributes\CreatesAttribute;
use Lunar\Core\Models\Attribute;

/**
 * Create an attribute. The `model_types` key attaches the attribute to the
 * attributable model types it should appear on.
 */
class CreateAttribute implements CreatesAttribute
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Attribute
    {
        $modelTypes = array_unique($attributes['model_types'] ?? []);
        unset($attributes['model_types']);

        $attributes['configuration'] ??= [];
        $attributes['system'] ??= false;
        $attributes['position'] ??= ((int) Attribute::query()->max('position')) + 1;

        /** @var Attribute $attribute */
        $attribute = Attribute::create($attributes);

        foreach ($modelTypes as $modelType) {
            $attribute->models()->create(['model_type' => $modelType]);
        }

        return $attribute;
    }
}
