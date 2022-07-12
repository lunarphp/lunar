<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Database\Factories\CollectionGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CollectionGroup extends BaseModel
{
    use HasFactory;
    use HasMacros;

    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\CollectionGroupFactory
     */
    protected static function newFactory(): CollectionGroupFactory
    {
        return CollectionGroupFactory::new();
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }
}
