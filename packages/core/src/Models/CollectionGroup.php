<?php

namespace Lunar\Models;

use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\CollectionGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CollectionGroup extends BaseModel
{
    use HasFactory;
    use HasMacros;

    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     *
     * @return \Lunar\Database\Factories\CollectionGroupFactory
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
