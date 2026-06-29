<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\FulfilmentLineFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property int $fulfilment_id
 * @property int $order_line_id
 * @property int $quantity
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class FulfilmentLine extends Base implements Contracts\FulfilmentLine
{
    use HasFactory;
    use HasMacros;
    use LogsActivity;

    /**
     * {@inheritDoc}
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return FulfilmentLineFactory::new();
    }

    /**
     * Return the fulfilment relationship.
     */
    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    /**
     * Return the order line relationship.
     */
    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLine::class);
    }
}
