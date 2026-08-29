<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Database\Factories\RefundLineFactory;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPublicId;
use Lunar\Core\Models\Concerns\LogsActivity;

/**
 * @property int $id
 * @property string $public_id
 * @property int $transaction_id
 * @property int $order_line_id
 * @property int $quantity
 * @property int $amount
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class RefundLine extends Base
{
    use HasFactory;
    use HasMacros;
    use HasPublicId;
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
        'amount' => 'integer',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return RefundLineFactory::new();
    }

    /**
     * The refund transaction this allocation belongs to.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Return the order line relationship.
     */
    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLine::class);
    }
}
