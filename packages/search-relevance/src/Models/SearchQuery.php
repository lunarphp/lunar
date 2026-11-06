<?php

namespace Lunar\SearchRelevance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Models\Base;
use Lunar\SearchRelevance\Database\Factories\SearchQueryFactory;

/**
 * @property string $id
 * @property string $model_type
 * @property string $raw_query
 * @property string $normalised_query
 * @property string $filters_hash
 * @property string $session_id
 * @property ?int $customer_id
 * @property string $version
 * @property string $mode
 * @property int $result_count
 * @property array<int, int> $shown
 * @property ?array<int, int> $ranked
 * @property ?array<int, array<string, mixed>> $features
 * @property ?Carbon $created_at
 */
class SearchQuery extends Base
{
    use HasFactory;
    use HasUlids;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'shown' => 'array',
        'ranked' => 'array',
        'features' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function newFactory(): SearchQueryFactory
    {
        return SearchQueryFactory::new();
    }

    public function events(): HasMany
    {
        return $this->hasMany(SearchEvent::class, 'search_id');
    }
}
