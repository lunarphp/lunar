<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasMacros;
use Lunar\Database\Factories\UrlFactory;

/**
 * @property int $id
 * @property int $language_id
 * @property string $element_type
 * @property int $element_id
 * @property string $slug
 * @property bool $default
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Url extends BaseModel implements \Lunar\Models\Contracts\Url
{
    use HasFactory;
    use HasMacros;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UrlFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Define attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'default' => 'boolean',
    ];

    /**
     * Return the element relationship.
     */
    public function element(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Return the language relationship.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::modelClass());
    }

    /**
     * Return the query scope for default.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->whereDefault(true);
    }
}
