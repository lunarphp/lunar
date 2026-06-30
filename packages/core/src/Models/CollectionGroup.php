<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\CollectionGroupFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class CollectionGroup extends Base
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
        return $this->hasMany(Collection::class);
    }
}
