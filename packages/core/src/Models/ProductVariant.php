<?php

namespace Lunar\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lunar\Core\Contracts\HasThumbnailImage;
use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Database\Factories\ProductVariantFactory;
use Lunar\Core\Models\Concerns\HasAttributeData;
use Lunar\Core\Models\Concerns\HasDimensions;
use Lunar\Core\Models\Concerns\HasMacros;
use Lunar\Core\Models\Concerns\HasPrices;
use Lunar\Core\Models\Concerns\HasStock;
use Lunar\Core\Models\Concerns\HasTranslations;
use Lunar\Core\Models\Concerns\LogsActivity;
use Spatie\LaravelBlink\BlinkFacade as Blink;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $product_id
 * @property int $tax_class_id
 * @property ?Collection $attribute_data
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
 * @property int $stock_on_hand
 * @property int $stock_incoming
 * @property int $stock_committed
 * @property int $stock_reserved
 * @property int $stock_unavailable
 * @property int $stock_available
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class ProductVariant extends Base implements Contracts\ProductVariant, HasThumbnailImage, Purchasable
{
    use HasAttributeData;
    use HasDimensions;
    use HasFactory;
    use HasMacros;
    use HasPrices;
    use HasStock;
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
        'shippable' => 'bool',
    ];

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ProductVariantFactory::new();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::modelClass());
    }

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::modelClass());
    }

    public function values(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');

        return $this->belongsToMany(
            ProductOptionValue::modelClass(),
            "{$prefix}product_option_value_product_variant",
            'variant_id',
            'value_id'
        )->withTimestamps();
    }

    public function getPrices(): Collection
    {
        $this->loadMissing(['prices.currency', 'prices.priceable']);

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
            $this->loadMissing('taxClass');

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
    public function requiresFulfilment(): bool
    {
        return $this->isShippable();
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return $this->product->translate('name');
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
        $this->loadMissing('values');

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
        $this->loadMissing(['images', 'product']);

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

    public function isPurchasable(): bool
    {
        return $this->product
            && (string) $this->product->status === 'published';
    }

    public function getTotalInventory(): int
    {
        if ($this->purchasable == 'in_stock') {
            return $this->stock;
        }

        return $this->stock + $this->backorder;
    }

    public function getThumbnailImage(): string
    {
        return $this->getThumbnail()?->getUrl('small') ?? '';
    }
}
