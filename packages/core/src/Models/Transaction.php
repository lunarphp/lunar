<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\Price;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\TransactionFactory;
use Lunar\Facades\Payments;

/**
 * @property int $id
 * @property ?int $parent_transaction_id
 * @property int $order_id
 * @property bool $success
 * @property string $type
 * @property string $driver
 * @property int $amount
 * @property string $reference
 * @property string $status
 * @property ?string $notes
 * @property string $card_type
 * @property ?string $last_four
 * @property ?array $meta
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class Transaction extends BaseModel
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
        'refund' => 'bool',
        'amount' => Price::class,
        'meta' => AsArrayObject::class,
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory(): TransactionFactory
    {
        return TransactionFactory::new();
    }

    /**
     * Return the order relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Return the currency relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function currency()
    {
        return $this->hasOneThrough(
            Currency::class,
            Order::class,
            'id',
            'code',
            'order_id',
            'currency_code'
        );
    }

    public function driver()
    {
        return Payments::driver($this->driver);
    }

    public function refund(int $amount, $notes = null)
    {
        return $this->driver()->refund($this, $amount, $notes);
    }

    public function capture(int $amount = 0)
    {
        return $this->driver()->capture($this, $amount);
    }
}
