<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\AttributeCache;
use Lunar\Core\Jobs\Attributes\PurgeAttributeData;
use Lunar\Core\Models\Contracts\Attribute as AttributeContract;

class AttributeObserver
{
    public function __construct(
        protected AttributeCache $cache,
    ) {}

    public function saved(AttributeContract $attribute): void
    {
        $this->cache->flush();
    }

    public function deleted(AttributeContract $attribute): void
    {
        $this->cache->flush();

        PurgeAttributeData::dispatch($attribute->id);
    }
}
