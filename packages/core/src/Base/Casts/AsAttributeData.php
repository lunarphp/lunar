<?php

namespace Lunar\Base\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use Lunar\Base\FieldType;
use Lunar\Exceptions\FieldTypeException;

class AsAttributeData implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                if (! isset($attributes[$key])) {
                    return null;
                }

                $data = json_decode($attributes[$key], true);

                $returnData = new Collection;

                foreach ($data as $key => $item) {
                    if (! class_exists($item['field_type'])) {
                        continue;
                    }
                    if (! in_array(FieldType::class, class_implements($item['field_type']))) {
                        throw new FieldTypeException('This field type is not supported.');
                    }
                    $returnData->put($key, new $item['field_type']($item['value']));
                }

                return $returnData;
            }

            public function set($model, $key, $value, $attributes)
            {
                $data = [];

                foreach ($value ?? [] as $handle => $item) {
                    $data[$handle] = [
                        'field_type' => get_class($item),
                        'value' => $item->getValue(),
                    ];
                }

                // JSON_THROW_ON_ERROR, because json_encode() reports failure by
                // returning false - on a value that is not valid UTF-8, or that
                // exceeds max depth. Bound into the update, that false is stored
                // as 0, which both destroys the attributes the row already held
                // and leaves it unreadable, since get() cannot iterate an int.
                return [$key => json_encode($data, JSON_THROW_ON_ERROR)];
            }
        };
    }
}
