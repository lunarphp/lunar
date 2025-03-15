<?php

namespace Lunar\Models;

use Illuminate\Support\Collection;

class ProductFilamentProxy extends Product
{
    protected $appends = [
        'variant_attributes',
    ];

    public function getVariantAttributesAttribute(): Collection
    {
        return $this->variants->first()->attribute_data;
    }
}
