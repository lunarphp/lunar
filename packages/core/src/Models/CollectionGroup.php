<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\CollectionGroupFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class CollectionGroup extends BaseModel
{
    use HasFactory;
    use HasMacros;

    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): CollectionGroupFactory
    {
        return CollectionGroupFactory::new();
    }

    /**
     * Return the collections relationship.
     */
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
