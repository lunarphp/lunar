<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasDefaultRecord;
use GetCandy\Base\Traits\HasMedia;
use GetCandy\Database\Factories\CustomerGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerGroup extends BaseModel
{
    use HasFactory, HasMedia, HasDefaultRecord;

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
