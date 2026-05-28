<?php

namespace Lunar\Core\Cache;

use Illuminate\Contracts\Cache\Repository;
use Lunar\Core\Contracts\AttributeCache as AttributeCacheContract;
use Lunar\Core\Contracts\FieldType;
use Lunar\Core\Contracts\FieldTypeManifest;
use Lunar\Core\Models\Attribute;

class AttributeCache implements AttributeCacheContract
{
    protected const KEY = 'lunar.attribute_cache';

    public function __construct(
        protected Repository $cache,
        protected FieldTypeManifest $fieldTypeManifest,
    ) {}

    public function getIdForHandle(string $handle): ?int
    {
        return $this->maps()['by_handle'][$handle] ?? null;
    }

    public function getHandleForId(int $id): ?string
    {
        return $this->maps()['by_id'][$id]['handle'] ?? null;
    }

    public function getFieldTypeClassForId(int $id): ?string
    {
        return $this->maps()['by_id'][$id]['field_type_class'] ?? null;
    }

    public function flush(): void
    {
        $this->cache->forget(static::KEY);
    }

    /**
     * @return array{by_handle: array<string, int>, by_id: array<int, array{handle: string, field_type_class: class-string<FieldType>|null}>}
     */
    protected function maps(): array
    {
        return $this->cache->rememberForever(static::KEY, function (): array {
            $byHandle = [];
            $byId = [];

            Attribute::query()
                ->get(['id', 'handle', 'type'])
                ->each(function (Attribute $attribute) use (&$byHandle, &$byId): void {
                    $byHandle[$attribute->handle] = $attribute->id;
                    $byId[$attribute->id] = [
                        'handle' => $attribute->handle,
                        'field_type_class' => $this->fieldTypeManifest->getType($attribute->type),
                    ];
                });

            return ['by_handle' => $byHandle, 'by_id' => $byId];
        });
    }
}
