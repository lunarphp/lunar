<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\StockMovementFactory;
use Lunar\Core\Enums\StockMovementType;
use Lunar\Core\Models\Concerns\HasMacros;

/**
 * An immutable, append-only entry in a variant's `on_hand` ledger.
 *
 * @property int $id
 * @property int $product_variant_id
 * @property int $location_id
 * @property int $quantity
 * @property StockMovementType $type
 * @property ?string $source_type
 * @property ?int $source_id
 * @property ?string $note
 * @property ?string $causer_type
 * @property ?int $causer_id
 * @property ?Carbon $created_at
 */
class StockMovement extends Base implements Contracts\StockMovement
{
    use HasFactory;
    use HasMacros;

    /**
     * The ledger is append-only — entries are never updated.
     */
    public const UPDATED_AT = null;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'type' => StockMovementType::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return StockMovementFactory::new();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::modelClass(), 'product_variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::modelClass());
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
