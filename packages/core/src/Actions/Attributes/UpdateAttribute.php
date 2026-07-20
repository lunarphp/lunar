<?php

namespace Lunar\Core\Actions\Attributes;

use Lunar\Core\Contracts\Actions\Attributes\UpdatesAttribute;
use Lunar\Core\Models\Attribute;

/**
 * Update an attribute. When supplied, the `model_types` key replaces which
 * attributable model types the attribute appears on.
 */
class UpdateAttribute implements UpdatesAttribute
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Attribute $attribute, array $attributes): Attribute
    {
        $modelTypes = array_key_exists('model_types', $attributes)
            ? array_unique((array) $attributes['model_types'])
            : null;
        unset($attributes['model_types']);

        $attribute->update($attributes);

        if ($modelTypes !== null) {
            $attribute->models()->delete();

            foreach ($modelTypes as $modelType) {
                $attribute->models()->create(['model_type' => $modelType]);
            }
        }

        return $attribute;
    }
}
