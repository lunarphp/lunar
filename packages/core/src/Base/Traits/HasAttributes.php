<?php

namespace GetCandy\Base\Traits;

use GetCandy\Models\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Get the attributes relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mappedAttributes(): HasMany
    {
        return $this->hasMany(Attribute::class, 'attribute_type', 'attributable_classname');
    }
}
