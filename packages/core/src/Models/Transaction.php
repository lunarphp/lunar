<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\Price;
use GetCandy\Base\Traits\LogsActivity;
use GetCandy\Database\Factories\TransactionFactory;
use GetCandy\Facades\Payments;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends BaseModel
{
    use HasFactory;
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
        'meta'   => 'object',
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\TransactionFactory
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
}
