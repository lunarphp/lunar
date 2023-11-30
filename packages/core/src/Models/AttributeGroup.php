<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Database\Factories\AttributeGroupFactory;

/**
 * @property int $id
 * @property string $attributable_type
 * @property string $name
 * @property string $handle
 * @property int $position
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class AttributeGroup extends BaseModel
{
    use HasFactory;
    use HasMacros;
    use HasTranslations;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        /**
         * Handle the AttributeGroup "saving" event.
         */
        static::saving(function (AttributeGroup $attributeGroup) {
            /**
             * If position is invalid set position to max value + 1
             */
            if (!($attributeGroup->position > 0) || AttributeGroup::where([
                ['id', '!=', $attributeGroup->id],
                ['attributable_type', '=', $attributeGroup->attributable_type],
                ['position', '=', $attributeGroup->position],
            ])->exists()) {
                $attributeGroup->position = AttributeGroup::where([
                    ['attributable_type', '=', $attributeGroup->attributable_type],
                ])->max('position') + 1;
            }
        });
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): AttributeGroupFactory
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
        'name' => AsCollection::class,
    ];

    /**
     * Return the attributes relationship.
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class)->orderBy('position');
    }
}
