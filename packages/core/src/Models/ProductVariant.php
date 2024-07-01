<?php

namespace Lunar\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Lunar\Base\BaseModel;
use Lunar\Base\Casts\AsAttributeData;
use Lunar\Base\Purchasable;
use Lunar\Base\Traits\HasAttributes;
use Lunar\Base\Traits\HasDimensions;
use Lunar\Base\Traits\HasMacros;
use Lunar\Base\Traits\HasPrices;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\LogsActivity;
use Lunar\Database\Factories\ProductVariantFactory;
use Spatie\LaravelBlink\BlinkFacade as Blink;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $product_id
 * @property int $tax_class_id
 * @property array $attribute_data
 * @property ?string $tax_ref
 * @property int $unit_quantity
 * @property int $min_quantity
 * @property int $quantity_increment
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
    use HasAttributes;
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
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Return the tax class relationship.
     */
    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * Return the related product option values.
     */
    public function values(): BelongsToMany
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

    public function getTaxReference(): ?string
    {
        return $this->tax_ref;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->shippable ? 'physical' : 'digital';
    }

    /**
     * {@inheritDoc}
     */
    public function isShippable(): bool
    {
        return $this->shippable;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return $this->product->translateAttribute('name');
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(): string
    {
        return $this->values->map(fn ($value) => $value->translate('name'))->join(', ');
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): Collection
    {
        return $this->values->map(fn ($value) => $value->translate('name'));
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return $this->sku;
    }

    public function images(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(Media::class, "{$prefix}media_product_variant")
            ->withPivot(['primary', 'position'])
            ->orderBy('position')
            ->withTimestamps();
    }

    public function getThumbnail(): ?Media
    {
        return $this->images->first(function ($media) {
            return (bool) $media->pivot?->primary;
        }) ?: $this->product->thumbnail;
    }

    public function canBeFulfilledAtQuantity(int $quantity): bool
    {
        if ($this->purchasable == 'always') {
            return true;
        }

        return $quantity <= $this->getTotalInventory();
    }

    public function getTotalInventory(): int
    {
        if ($this->purchasable == 'in_stock') {
            return $this->stock;
        }

        return $this->stock + $this->backorder;
    }
}
