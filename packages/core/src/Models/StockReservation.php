<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Contracts\Actions\Products\CommitsReservation;
use Lunar\Core\Contracts\Actions\Products\ReleasesReservation;
use Lunar\Core\Database\Factories\StockReservationFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;

/**
 * A time-boxable hold a checkout places against a variant before an order
 * exists, so two concurrent checkouts cannot both claim the last unit.
 *
 * Global — a cart picks no location — so it lives on the variant rollup
 * (`stock_reserved`), never on a `StockLevel`.
 *
 * @property int $id
 * @property string $public_id
 * @property int $product_variant_id
 * @property int $quantity
 * @property ?string $reference_type
 * @property ?int $reference_id
 * @property ?Carbon $expires_at
 * @property ?Carbon $released_at
 * @property ?Carbon $committed_at
 * @property ?string $note
 * @property-read bool $is_active
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class StockReservation extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'quantity' => 'integer',
        'expires_at' => 'datetime',
        'released_at' => 'datetime',
        'committed_at' => 'datetime',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return StockReservationFactory::new();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Whether the reservation still holds stock (not released, not committed,
     * not expired). The `stock_reserved` rollup sums active reservations.
     */
    public function getIsActiveAttribute(): bool
    {
        return blank($this->released_at)
            && blank($this->committed_at)
            && (blank($this->expires_at) || $this->expires_at->isFuture());
    }

    /**
     * Limit the query to reservations still holding stock.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('released_at')
            ->whereNull('committed_at')
            ->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function release(): StockReservation
    {
        return app(ReleasesReservation::class)->execute($this);
    }

    public function commit(): StockReservation
    {
        return app(CommitsReservation::class)->execute($this);
    }
}
