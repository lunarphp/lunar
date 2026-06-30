<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\StockLevelFactory;
use Lunar\Core\Models\Concerns\HasMacros;

/**
 * A variant's stock balance at a single location.
 *
 * @property int $id
 * @property int $product_variant_id
 * @property int $location_id
 * @property int $on_hand
 * @property int $incoming
 * @property int $committed
 * @property int $unavailable
 * @property ?\ArrayObject $meta
 * @property-read int $available
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class StockLevel extends Base
{
    use HasFactory;
    use HasMacros;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'on_hand' => 'integer',
        'incoming' => 'integer',
        'committed' => 'integer',
        'unavailable' => 'integer',
        'meta' => AsArrayObject::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return StockLevelFactory::new();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Scoped to this level's location — movements are keyed by variant + location,
     * not by a level FK. Intended for per-instance access, not eager loading.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_variant_id', 'product_variant_id')
            ->where('location_id', $this->location_id);
    }

    public function getAvailableAttribute(): int
    {
        return $this->on_hand - $this->committed - $this->unavailable;
    }
}
