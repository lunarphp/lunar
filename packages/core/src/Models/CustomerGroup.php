<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasDefaultRecord;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\HasMedia;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Database\Factories\CustomerGroupFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerGroup extends BaseModel
{
    use HasFactory;
    use HasMedia;
    use HasDefaultRecord;
    use HasMacros;
    use HasTranslations;

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'name' => AsCollection::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\CustomerGroupFactory
     */
    protected static function newFactory(): CustomerGroupFactory
    {
        return CustomerGroupFactory::new();
    }
}
