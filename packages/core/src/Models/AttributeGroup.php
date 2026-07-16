<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Lunar\Core\Database\Factories\AttributeGroupFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $public_id
 * @property string $name
 * @property string $handle
 * @property int $position
 * @property bool $system
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class AttributeGroup extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;
    use LogsActivity;

    protected static function booted(): void
    {
        static::deleting(function (self $group) {
            Attribute::query()
                ->where('attribute_group_id', $group->id)
                ->update(['attribute_group_id' => null]);
        });
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return AttributeGroupFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Define which attributes should be cast.
     *
     * @var array
     */
    protected $casts = [
        'system' => 'bool',
    ];

    public function setHandleAttribute(string $value): void
    {
        $this->attributes['handle'] = Str::slug($value, '_');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class)->orderBy('position');
    }
}
