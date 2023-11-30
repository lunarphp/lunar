<?php

namespace Lunar\Base\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Models\Attribute;

trait HasAttributes
{
    /**
     * Method when trait is initialized.
     *
     * @return void
     */
    public function initializeHasAttributes(): void
    {
        $this->mergeCasts([
            'attribute_data' => AsAttributeData::class,
        ]);
    }

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
     */
    public function mappedAttributes(): HasMany
    {
        return $this->hasMany(Attribute::class, 'attribute_type', 'attributable_classname');
    }
}
