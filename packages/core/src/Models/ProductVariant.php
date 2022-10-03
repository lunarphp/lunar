<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\HasDimensions;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasPrices;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\ProductVariantFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends BaseModel implements Purchasable
{
    use HasFactory;
    use HasPrices;
    use LogsActivity;
    use HasDimensions;
    use HasTranslations;
    use HasMacros;

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
     * @return \Lunar\Database\Factories\ProductVariantFactory
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
        return $this->belongsTo(Product::class)->withTrashed();
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
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            ProductOptionValue::class,
            "{$prefix}product_option_value_product_variant",
            'variant_id',
            'value_id'
        )->withTimestamps();
    }

    public function getPrices(): Collection
    {
        return $this->prices;
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
     * @return \Lunar\Models\TaxClass
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
    public function getOptions()
    {
        return $this->values->map(fn ($value) => $value->translate('name'));
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->sku;
    }

    public function images()
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(Media::class, "{$prefix}media_product_variant")
            ->withPivot(['primary', 'position'])
            ->orderBy('position')
            ->withTimestamps();
    }

    public function getThumbnail()
    {
        $thumbnail = $this->images()->wherePivot('primary', true)?->first();

        if (!$thumbnail) {
            return $this->product->thumbnail;
        }

        return $thumbnail;
    }
}
