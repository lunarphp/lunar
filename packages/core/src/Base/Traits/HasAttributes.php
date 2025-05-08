<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Models\Attribute;

trait HasAttributes
{
    /**
     * Getter to return the class name used with attribute relationships.
     *
     * @return string
     */
    public function getAttributableClassnameAttribute()
    {
        return self::class;
    }

    public function getAttributableMorphMapAttribute()
    {
        return self::morphName();
    }

    /**
     * Get the attributes relation.
     */
    public function mappedAttributes(): HasMany
    {
        return $this->hasMany(Attribute::modelClass(), 'attribute_type', 'attributable_morph_map');
    }
}
