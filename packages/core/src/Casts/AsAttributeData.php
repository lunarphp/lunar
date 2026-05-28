<?php

namespace Lunar\Core\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\AttributeCache;
use Lunar\Core\Contracts\FieldType;

class AsAttributeData implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array<int, mixed>  $arguments
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            protected function cache(): AttributeCache
            {
                return app(AttributeCache::class);
            }

            /**
             * Hydrate the id-keyed raw JSON into a handle-keyed collection of FieldType instances.
             */
            public function get($model, $key, $value, $attributes): ?Collection
            {
                if (! isset($attributes[$key])) {
                    return null;
                }

                $data = json_decode($attributes[$key], true) ?: [];

                $cache = $this->cache();
                $returnData = new Collection;

                foreach ($data as $id => $rawValue) {
                    $id = (int) $id;
                    $handle = $cache->getHandleForId($id);
                    $fieldTypeClass = $cache->getFieldTypeClassForId($id);

                    if (! $handle || ! $fieldTypeClass) {
                        continue;
                    }

                    $returnData->put($handle, new $fieldTypeClass($rawValue));
                }

                return $returnData;
            }

            /**
             * Serialize a handle/id-keyed collection of FieldType instances into id-keyed raw JSON.
             *
             * @return array<string, string>
             */
            public function set($model, $key, $value, $attributes): array
            {
                $cache = $this->cache();
                $data = [];

                foreach ($value ?? [] as $reference => $item) {
                    $id = is_numeric($reference)
                        ? (int) $reference
                        : $cache->getIdForHandle((string) $reference);

                    if (! $id) {
                        continue;
                    }

                    $data[(string) $id] = $item instanceof FieldType ? $item->jsonSerialize() : $item;
                }

                return [$key => json_encode($data)];
            }
        };
    }
}
