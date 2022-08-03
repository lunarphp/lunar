<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Database\Factories\AttributeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class Attribute extends BaseModel
{
    use HasFactory;
    use HasTranslations;
    use HasMacros;

    public static function boot()
    {
        static::deleting(function (Model $model) {
            DB::table(
                config('getcandy.database.table_prefix').'attributables'
            )->where('attribute_id', '=', $model->id)->delete();
        });
        parent::boot();
    }

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\AttributeFactory
     */
    protected static function newFactory(): AttributeFactory
    {
        return AttributeFactory::new();
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
        'name'          => AsCollection::class,
        'configuration' => AsCollection::class,
    ];

    /**
     * Return the attribuable relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Returns the attribute group relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    /**
     * Apply the system scope to the query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return void
     */
    public function scopeSystem(Builder $query, $type)
    {
        return $query->whereAttributeType($type)->whereSystem(true);
    }
}
