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
use Lunar\Exceptions\InsufficientStockException;
use Spatie\LaravelBlink\BlinkFacade as Blink;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $product_id
 * @property int $tax_class_id
 * @property array $attribute_data
 * @property ?string $tax_ref
 * @property int $unit_quantity
 * @property ?string $sku
 * @property ?string $gtin
 * @property ?string $mpn
 * @property ?string $ean
 * @property ?float $length_value
 * @property ?string $length_unit
 * @property ?float $width_value
 * @property ?string $width_unit
 * @property ?float $height_value
 * @property ?string $height_unit
 * @property ?float $weight_value
 * @property ?string $weight_unit
 * @property ?float $volume_value
 * @property ?string $volume_unit
 * @property bool $shippable
 * @property int $stock
 * @property int $backorder
 * @property string $purchasable
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property ?\Illuminate\Support\Carbon $deleted_at
 */
class ProductVariant extends BaseModel implements Purchasable
{
    use HasDimensions;
    use HasFactory;
    use HasMacros;
    use HasPrices;
    use HasTranslations;
    use LogsActivity;

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
        'attribute_data' => AsAttributeData::class,
    ];

    /**
     * Return a new factory instance for the model.
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
     */
    public function getUnitQuantity(): int
    {
        return $this->unit_quantity;
    }

    /**
     * Return the tax class.
     */
    public function getTaxClass(): TaxClass
    {
        return Blink::once("tax_class_{$this->tax_class_id}", function () {
            return $this->taxClass;
        });
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
        return $this->images->first(function ($media) {
            return (bool) $media->pivot?->primary;
        }) ?: $this->product->thumbnail;
    }

    /**
     * @throws InsufficientStockException
     */
    public function deductStock(int $amount): void
    {
        if ($this->stock - $amount < 0 && $this->purchasable === 'in_stock') {
            throw new InsufficientStockException('Insufficient stock for product variant with EAN ' . $this->ean);
        }

        $this->stock -= $amount;
        $this->save();
    }
}
