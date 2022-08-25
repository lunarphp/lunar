<?php

namespace GetCandy\Base\Casts;

use GetCandy\Base\FieldType;
use GetCandy\Exceptions\FieldTypeException;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;

class AsAttributeData implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new class() implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                if (! isset($attributes[$key])) {
                    return null;
                }

                $data = json_decode($attributes[$key], true);

                $returnData = new Collection();

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
                        'value'      => $item->getValue(),
                    ];
                }

                return [$key => json_encode($data)];
            }
        };
    }
}
