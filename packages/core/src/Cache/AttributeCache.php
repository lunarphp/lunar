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

    /**
     * The resolved maps for this instance.
     *
     * The cast on every attribute-backed model resolves a handle and a field
     * type per attribute, so a single page can ask for the maps a hundred
     * times. Without this, each call is a cache read -- on the database cache
     * driver, a hundred queries.
     *
     * The binding is scoped, so the memo lives for exactly one request or
     * queue job and every new scope re-reads the shared store. A process-long
     * memo would be cheaper still, but a stale map silently drops attribute
     * values in AsAttributeData's set path, so cross-process staleness is not
     * a trade worth taking.
     *
     * @var array{by_handle: array<string, int>, by_id: array<int, array{handle: string, field_type_class: class-string<FieldType>|null}>}|null
     */
    protected ?array $maps = null;

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
        $this->maps = null;

        $this->cache->forget(static::KEY);
    }

    /**
     * @return array{by_handle: array<string, int>, by_id: array<int, array{handle: string, field_type_class: class-string<FieldType>|null}>}
     */
    protected function maps(): array
    {
        return $this->maps ??= $this->cache->rememberForever(static::KEY, function (): array {
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
