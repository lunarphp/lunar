<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\FulfilmentFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\States\Fulfilment\FulfilmentState;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property int $order_id
 * @property int $location_id
 * @property ?string $reference
 * @property FulfilmentState $state
 * @property ?string $notes
 * @property ?array $meta
 * @property ?Carbon $shipped_at
 * @property ?Carbon $held_at
 * @property ?string $hold_reason
 * @property ?string $hold_note
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Fulfilment extends Base implements Contracts\Fulfilment
{
    use HasFactory;
    use HasMacros;
    use HasStates;
    use LogsActivity;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'state' => FulfilmentState::class,
        'shipped_at' => 'datetime',
        'held_at' => 'datetime',
        'meta' => AsArrayObject::class,
    ];

    /**
     * Whether the fulfilment is currently on hold (blocked from shipping).
     */
    public function isOnHold(): bool
    {
        return ! blank($this->held_at);
    }

    /**
     * The human-readable label for the current hold reason, resolved from the
     * configured reason list (falls back to the stored key).
     */
    public function holdReasonLabel(): ?string
    {
        if (blank($this->hold_reason)) {
            return null;
        }

        return config('lunar.fulfilment.hold_reasons.'.$this->hold_reason, $this->hold_reason);
    }

    /**
     * Limit the query to fulfilments currently on hold.
     */
    public function scopeOnHold(Builder $query): Builder
    {
        return $query->whereNotNull('held_at');
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return FulfilmentFactory::new();
    }

    /**
     * Return the order relationship.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::modelClass());
    }

    /**
     * Return the location relationship.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::modelClass());
    }

    /**
     * Return the fulfilment lines relationship.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(FulfilmentLine::modelClass());
    }

    /**
     * Return the tracking references relationship.
     */
    public function trackings(): HasMany
    {
        return $this->hasMany(FulfilmentTracking::modelClass());
    }
}
