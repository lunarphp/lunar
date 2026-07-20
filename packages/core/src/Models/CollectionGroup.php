<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Database\Factories\CollectionGroupFactory;
use Lunar\Core\Exceptions\CollectionGroupActionException;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $public_id
 * @property string $name
 * @property string $handle
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class CollectionGroup extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use LogsActivity;

    protected $guarded = [];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return CollectionGroupFactory::new();
    }

    protected static function booted(): void
    {
        static::creating(function (self $group) {
            if (blank($group->handle)) {
                $group->handle = static::uniqueHandle($group->name ?? '');
            }
        });

        // The guard lives on the model, not just the admin actions, so every
        // delete path (Eloquent, bulk actions, consumer code) refuses while
        // collections still sit in the group — move or delete them first.
        static::deleting(function (self $group) {
            if ($group->collections()->exists()) {
                throw new CollectionGroupActionException(
                    'Collection group has collections — move or delete them before deleting.'
                );
            }
        });
    }

    /**
     * Generate a kebab-case handle from the name, suffixed until unique:
     * group, group-2, group-3, ...
     */
    protected static function uniqueHandle(string $name): string
    {
        $base = Str::slug($name) ?: 'collection-group';
        $handle = $base;

        for ($suffix = 2; static::where('handle', $handle)->exists(); $suffix++) {
            $handle = $base.'-'.$suffix;
        }

        return $handle;
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
