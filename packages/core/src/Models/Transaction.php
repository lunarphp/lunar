<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;
use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Database\Factories\TransactionFactory;
use Lunar\Core\Facades\Payments;
use Lunar\Core\Models\Concerns\FormatsPrices;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;

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
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Transaction extends Base implements HasCurrency
{
    use FormatsPrices;
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
        'amount' => 'integer',
        'meta' => AsArrayObject::class,
    ];

    public function resolveCurrency(): Currency
    {
        $this->loadMissing('order.currency');

        return $this->order?->currency ?? Currency::getDefault();
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TransactionFactory::new();
    }

    /**
     * Return the order relationship.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Return the currency relationship.
     */
    public function currency(): HasOneThrough
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

    public function paymentChecks()
    {
        return $this->driver()->getPaymentChecks($this);
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
