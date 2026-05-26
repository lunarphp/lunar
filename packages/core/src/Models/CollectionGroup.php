<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Base\BaseModel;
use Lunar\Core\Concerns\HasMacros;
use Lunar\Core\Concerns\LogsActivity;
use Lunar\Core\Database\Factories\CollectionGroupFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class CollectionGroup extends BaseModel implements Contracts\CollectionGroup
{
    use HasFactory;
    use HasMacros;
    use LogsActivity;

    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CollectionGroupFactory::new();
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::modelClass());
    }
}
