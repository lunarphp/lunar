<?php

namespace Lunar\SearchRelevance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Lunar\Core\Models\Base;
use Lunar\SearchRelevance\Database\Factories\SearchQueryScoreFactory;

/**
 * Read model over the aggregated scores. The table has a composite primary
 * key, so write through the query builder (the aggregators upsert), not save().
 *
 * @property string $model_type
 * @property string $normalised_query
 * @property int $product_id
 * @property float $score
 * @property float $relative
 * @property int $sessions
 * @property string $version
 * @property ?Carbon $updated_at
 */
class SearchQueryScore extends Base
{
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $guarded = [];

    protected $casts = [
        'product_id' => 'integer',
        'score' => 'float',
        'relative' => 'float',
        'sessions' => 'integer',
        'updated_at' => 'datetime',
    ];

    protected static function newFactory(): SearchQueryScoreFactory
    {
        return SearchQueryScoreFactory::new();
    }
}
