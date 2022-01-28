<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\AsAttributeData;
use GetCandy\Base\Purchasable;
use GetCandy\Base\Traits\HasDimensions;
use GetCandy\Base\Traits\HasMedia;
use GetCandy\Base\Traits\HasPrices;
use GetCandy\Database\Factories\ProductVariantFactory;
use GetCandy\Exceptions\MissingCurrencyPriceException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;

class ProductVariant extends BaseModel implements SpatieHasMedia, Purchasable
{
    use HasFactory;
    use HasMedia;
    use HasPrices;
    use HasDimensions;

    /**
     * Define the guarded attributes.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'requires_shipping' => 'bool',
        'attribute_data'    => AsAttributeData::class,
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\ProductVariantFactory
     */
    protected static function newFactory(): ProductVariantFactory
    {
        return ProductVariantFactory::new();
    }

    /**
     * The related product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Return the tax class relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * Return the related product option values.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function values()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(
            ProductOptionValue::class,
            "{$prefix}product_option_value_product_variant",
            'variant_id',
            'value_id'
        )->withTimestamps();
    }

    /**
     * Get the price based on quantity and customer groups.
     *
     * @param  int  $quantity
     * @param  \Illuminate\Support\Collection  $customerGroups
     * @return int
     */
    public function getPrice(
        $quantity,
        Currency $currency,
        Collection $customerGroups = null
    ): int {
        if (! $customerGroups) {
            $customerGroups = collect();
        }

        $prices = $this->prices->filter(function ($price) use ($quantity, $customerGroups) {
            return ($price->tier <= $quantity) && (
                ! $price->customer_group_id || $customerGroups->pluck('id')->contains($price->customer_group_id)
            );
        })->sortBy('price');

        $currencyPrice = $prices->first(function ($price) use ($currency) {
            return $price->currency_id == $currency->id;
        });

        if (! $currencyPrice) {
            throw new MissingCurrencyPriceException(
                __('getcandy::exceptions.missing_currency_price', [
                    'currency' => $currency->code,
                ])
            );
        }

        return $currencyPrice->price->value;
    }

    /**
     * Return the unit quantity for the variant.
     *
     * @return int
     */
    public function getUnitQuantity(): int
    {
        return $this->unit_quantity;
    }

    /**
     * Return the tax class.
     *
     * @return \GetCandy\Models\TaxClass
     */
    public function getTaxClass(): TaxClass
    {
        return $this->taxClass;
    }

    public function getTaxReference()
    {
        return $this->tax_ref;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->shippable ? 'physical' : 'digital';
    }

    /**
     * {@inheritDoc}
     */
    public function isShippable()
    {
        return $this->shippable;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->product->translateAttribute('name');
    }

    /**
     * {@inheritDoc}
     */
    public function getOption()
    {
        return $this->values->map(fn ($value) => $value->translate('name'))->join(', ');
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->sku;
    }

    /**
     * {@inheritDoc}
     */
    public function getThumbnail()
    {
        if ($thumbnail = $this->product->thumbnail) {
            return $thumbnail->getUrl('small');
        }

        return null;
    }
}
