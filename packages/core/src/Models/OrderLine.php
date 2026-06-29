<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Lunar\Core\Casts\TaxBreakdown;
use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Database\Factories\OrderLineFactory;
use Lunar\Core\Models\Concerns\FormatsPrices;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\LogsActivity;
use Lunar\Core\Models\Contracts\Currency as CurrencyContract;

/**
 * @property int $id
 * @property int $order_id
 * @property string $purchasable_type
 * @property int $purchasable_id
 * @property string $type
 * @property bool $requires_shipping
 * @property bool $requires_fulfilment
 * @property string $description
 * @property ?string $option
 * @property string $identifier
 * @property int $unit_price
 * @property int $unit_quantity
 * @property int $quantity
 * @property int $sub_total
 * @property int $discount_total
 * @property array $tax_breakdown
 * @property int $tax_total
 * @property int $total
 * @property ?string $notes
 * @property ?array $meta
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class OrderLine extends Base implements Contracts\OrderLine, HasCurrency
{
    use FormatsPrices;
    use HasFactory;
    use HasMacros;
    use LogsActivity;

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return OrderLineFactory::new();
    }

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'requires_shipping' => 'boolean',
        'requires_fulfilment' => 'boolean',
        'unit_quantity' => 'integer',
        'quantity' => 'integer',
        'meta' => AsArrayObject::class,
        'tax_breakdown' => TaxBreakdown::class,
        'unit_price' => 'integer',
        'sub_total' => 'integer',
        'tax_total' => 'integer',
        'discount_total' => 'integer',
        'total' => 'integer',
    ];

    public function resolveCurrency(): CurrencyContract
    {
        $this->loadMissing('order.currency');

        return $this->order?->currency ?? Currency::getDefault();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function fulfilmentLines(): HasMany
    {
        return $this->hasMany(FulfilmentLine::class);
    }

    /**
     * Limit the query to order lines not yet allocated to any fulfilment.
     */
    public function scopeWithoutFulfilment(Builder $query): Builder
    {
        return $query->whereDoesntHave('fulfilmentLines');
    }

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

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
}
