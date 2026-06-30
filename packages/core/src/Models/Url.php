<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\UrlFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\InvalidatesRelatedCache;

/**
 * @property int $id
 * @property int $language_id
 * @property string $element_type
 * @property int $element_id
 * @property string $slug
 * @property bool $default
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Url extends Base
{
    use HasFactory;
    use HasMacros;
    use InvalidatesRelatedCache;

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

    public function cacheInvalidationTargets(): iterable
    {
        $this->loadMissing('element');

        return [$this->element];
    }

    /**
     * Return the language relationship.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Return the query scope for default.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->whereDefault(true);
    }
}
