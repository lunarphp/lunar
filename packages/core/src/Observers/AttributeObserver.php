<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\AttributeCache;
use Lunar\Core\Jobs\Attributes\PurgeAttributeData;
use Lunar\Core\Models\Attribute;

class AttributeObserver
{
    public function __construct(
        protected AttributeCache $cache,
    ) {}

    public function saved(Attribute $attribute): void
    {
        $this->cache->flush();
    }

    public function deleted(Attribute $attribute): void
    {
        $this->cache->flush();

        PurgeAttributeData::dispatch($attribute->id);
    }
}
