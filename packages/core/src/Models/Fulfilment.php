<?php

namespace Lunar\Core\Models;

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
 * @property ?int $location_id
 * @property string $reference
 * @property FulfilmentState $state
 * @property ?string $shipping_method
 * @property ?string $tracking_number
 * @property ?string $tracking_url
 * @property ?string $notes
 * @property ?array $meta
 * @property ?Carbon $shipped_at
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
        'meta' => AsArrayObject::class,
    ];

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
}
