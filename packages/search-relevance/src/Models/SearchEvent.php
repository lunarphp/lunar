<?php

namespace Lunar\SearchRelevance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Models\Base;
use Lunar\SearchRelevance\Database\Factories\SearchEventFactory;

/**
 * @property int $id
 * @property string $search_id
 * @property int $product_id
 * @property int $position
 * @property string $type
 * @property string $source
 * @property string $session_id
 * @property ?Carbon $created_at
 */
class SearchEvent extends Base
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'product_id' => 'integer',
        'position' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function newFactory(): SearchEventFactory
    {
        return SearchEventFactory::new();
    }

    public function searchQuery(): BelongsTo
    {
        return $this->belongsTo(SearchQuery::class, 'search_id');
    }
}
